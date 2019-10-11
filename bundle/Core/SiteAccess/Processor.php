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
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use Kaliop\eZMigrationBundle\API\Collection\MigrationDefinitionCollection;
use Kaliop\eZMigrationBundle\Core\MigrationService;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\WrapperFactoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

final class Processor
{
    use SiteAccessRegistryAware;
    use EzRepositoryAware;
    use WrapperFactoryAware;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MigrationService
     */
    private $migrationService;

    public function __construct(Environment $twig, MigrationService $migrationService)
    {
        $this->twig = $twig;
        $this->migrationService = $migrationService;
    }

    public function __invoke(SiteConfiguration $configuration)
    {
        $rootLocation = $this->createNewSiteAccessData($configuration);
        $configuration->setRoot($this->wrapperFactory->createByLocation($rootLocation));
        $this->createNewSiteAccessConfiguration($configuration, $rootLocation);
    }

    private function createNewSiteAccessConfiguration(SiteConfiguration $configuration, Location $rootLocation): void
    {
        $configJson = [
            'design' => $configuration->getDesign(),
            'cache_service_name' => $configuration->getCachePool(),
            'languages' => $configuration->getPrioritizedLanguges(),
            'groups' => $configuration->getGroups(),
            'page_builder' => $configuration->getPageBuilderGroup(),
            'root_location_id' => $rootLocation->id,
        ];

        $fs = new Filesystem();
        $fs->dumpFile(
            "{$this->siteAccessDirectory}/{$configuration->getSiteaccessName()}.json",
            json_encode($configJson, JSON_PRETTY_PRINT)
        );
    }

    private function generateData(SiteConfiguration $configuration): array
    {
        return [
            'rootLocationPath' => $configuration->getRootLocationId() > 0 ? $configuration->getRoot(
            )->location->pathString : null,
            'name' => $configuration->getName(),
            'siteaccessName' => $configuration->getSiteaccessName(),
            'adminEmail' => $configuration->getAdminEmail(),
            'adminPassword' => 'Publish',
            'lang' => $configuration->getPrioritizedLanguges()[0],
            'create_user' => false,
            'key' => "k{$configuration->getId()}",
        ];
    }

    private function createNewSiteAccessData(SiteConfiguration $configuration): Location
    {
        $data = $this->generateData($configuration);
        try {
            $user = $this->repository->getUserService()->loadUserByLogin($configuration->getAdminEmail());
            $groups = $this->repository->getUserService()->loadUserGroupsOfUser($user);
            $data['user'] = $user;
            $data['groupIds'] = array_map(
                function (UserGroup $group) {
                    return $group->id;
                },
                $groups
            );
        } catch (NotFoundException $exception) {
            $data['user'] = null;
            $data['groupIds'] = null;
        }

        $content = $this->twig->render('@ezdesign/site_configuration/siteaccess.yaml.twig', $data);
        $fs = new Filesystem();
        $path = "{$this->siteAccessCacheDirectory}/".time()."{$configuration->getSiteaccessName()}_data.yaml";
        $fs->dumpFile($path, $content);
        /** @var MigrationDefinitionCollection $migrationDefinitions */
        $migrationDefinitions = $this->migrationService->getMigrationsDefinitions([$path]);
        foreach ($migrationDefinitions as $migrationDefinition) {
            $migration = $this->migrationService->parseMigrationDefinition($migrationDefinition);
            $this->migrationService->executeMigration($migration);
        }

        if ($configuration->getRootLocationId() > 0) {
            return $configuration->getRoot()->location;
        }

        return $this->repository->getLocationService()->loadLocationByRemoteId(
            "novaezsiteaccessfactory-sa-top-location-{$configuration->getSiteaccessName()}"
        );
    }
}
