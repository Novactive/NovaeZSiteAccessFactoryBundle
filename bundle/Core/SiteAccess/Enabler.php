<?php

/**
 * Nova eZ SiteAccess Factory Bundle.
 *
 * @author    Sébastien Morel aka Plopix <morel.seb@gmail.com>
 * @author    Novactive <dir.tech@novactive.com>
 * @copyright 2019 Novactive
 * @license   https://github.com/Novactive/NovaeZSiteAccessFactoryBundle/blob/master/LICENSE MIT Licence
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\Limitation\SiteAccessLimitation;
use eZ\Publish\API\Repository\Values\User\Policy;
use eZ\Publish\API\Repository\Values\User\RoleDraft;
use eZ\Publish\Core\Repository\Values\User\PolicyUpdateStruct;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;

final class Enabler
{
    use SiteAccessRegistryAware;
    use EzRepositoryAware;

    /**
     * @var array
     */
    private $siteAccessList;

    /**
     * @todo: could be injected from the configuration
     */
    private const ANONYMOUS_ROLE_ID = 1;

    public function __construct(array $siteAccessList)
    {
        foreach ($siteAccessList as $sa) {
            $this->siteAccessList[$this->generateSiteAccessValue($sa)] = $sa;
        }
    }

    public function __invoke(SiteConfiguration $configuration)
    {
        $this->manageAnonymousRoleAccess($configuration->getSiteaccessName(), 'add');
    }

    public function reverse(SiteConfiguration $configuration): void
    {
        $this->manageAnonymousRoleAccess($configuration->getSiteaccessName(), 'remove');
    }

    private function getAnonymousDraftRole(): RoleDraft
    {
        $roleService = $this->repository->getRoleService();
        $role = $roleService->loadRole(self::ANONYMOUS_ROLE_ID);
        try {
            $roleDraft = $roleService->loadRoleDraftByRoleId(self::ANONYMOUS_ROLE_ID);
        } catch (NotFoundException $e) {
            // The draft doesn't exist, let's create one
            $roleDraft = $roleService->createRoleDraft($role);
        }

        return $roleDraft;
    }

    private function manageAnonymousRoleAccess(string $siteAccessIndentifier, string $action): void
    {
        $roleService = $this->repository->getRoleService();
        $roleDraft = $this->getAnonymousDraftRole();

        /** @var Policy[] $policies */
        $policies = $roleDraft->getPolicies();
        foreach ($policies as $policy) {
            if ('user' === $policy->module && 'login' === $policy->function) {
                $updateStruct = $this->updateUserLoginPolicy($policy, $siteAccessIndentifier, $action);
                // note: no way to retrieve a PolicyDraft here, PhpStorm is complaining but that is working and OK!
                $roleService->updatePolicyByRoleDraft($roleDraft, $policy, $updateStruct);
                $roleService->publishRoleDraft($roleDraft);

                return;
            }
        }
    }

    private function updateUserLoginPolicy(Policy $policy, string $siteAccessIndentifier, $action): PolicyUpdateStruct
    {
        $siteaccessCrc = $this->generateSiteAccessValue($siteAccessIndentifier);
        $updateStruct = new PolicyUpdateStruct();

        /** @var Limitation[] $limitations */
        $limitations = $policy->getLimitations();
        foreach ($limitations as $limitation) {
            if ($limitation instanceof SiteAccessLimitation) {
                if (!\in_array($siteaccessCrc, $limitation->limitationValues) && 'add' === $action) {
                    $limitation->limitationValues[] = $siteaccessCrc;
                }

                if (\in_array($siteaccessCrc, $limitation->limitationValues) && 'remove' === $action) {
                    foreach ($limitation->limitationValues as $key => $value) {
                        if ($value === $siteaccessCrc) {
                            unset($limitation->limitationValues[$key]);
                        }
                    }
                }

                // sanity check on the SiteAccess
                foreach ($limitation->limitationValues as $key => $value) {
                    if (!isset($this->siteAccessList[$value])) {
                        unset($limitation->limitationValues[$key]);
                    }
                }
            }
            $updateStruct->addLimitation($limitation);
        }

        return $updateStruct;
    }

    private function generateSiteAccessValue(string $siteAccessIndentifier): string
    {
        return sprintf('%u', crc32($siteAccessIndentifier));
    }
}
