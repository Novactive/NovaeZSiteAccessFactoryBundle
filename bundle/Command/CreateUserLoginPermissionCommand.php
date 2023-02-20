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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Command;

use Exception;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SiteAccessLimitation;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class CreateUserLoginPermissionCommand extends Command
{
    use EzRepositoryAware;

    /**
     * @var string
     */
    public static $defaultName = 'novaezsiteaccessfactory:create:userlogin:permissions';

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->addArgument('siteaccess', InputArgument::REQUIRED, 'Siteaccess name')
            ->setDescription('Create a Role and Assign to the correct Group.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $siteAccessIdentifier = $input->getArgument('siteaccess');

        $io->title("Adding Role and Permissions for {$siteAccessIdentifier}");
        try {
            // Give access to admin on the newly available siteacess
            // Must be done afterwards...
            $roleService = $this->repository->getRoleService();
            $userService = $this->repository->getUserService();
            $identifier = "PermissionsLoginFor-{$siteAccessIdentifier}";
            try {
                $roleService->loadRoleByIdentifier($identifier);
            } catch (NotFoundException $exception) {
                $roleStruct = $roleService->newRoleCreateStruct($identifier);
                $policyStruct = $roleService->newPolicyCreateStruct('user', 'login');
                // TODO check this
//                $limitation = new SiteAccessLimitation();
//                $limitation->limitationValues[] = sprintf('%u', crc32($siteAccessIdentifier));
//                $policyStruct->addLimitation($limitation);
                $roleStruct->addPolicy($policyStruct);
                $roleDraft = $roleService->createRole($roleStruct);
                $roleService->publishRoleDraft($roleDraft);
                $group = $this->repository->getContentService()->loadContentByRemoteId(
                    "novaezsiteaccessfactory-sa-top-group-{$siteAccessIdentifier}"
                );
                $roleService->assignRoleToUserGroup($roleDraft, $userService->loadUserGroup($group->id));
                $io->success('Done.');

                return 0;
            }
        } catch (Exception $e) {
            // if it is not working we don't kill the process, that is fine it can be done manually
            $io->error($e->getMessage());

            return 1;
        }

        $io->comment('Nothing was done, it was already done.');

        return 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $input; // phpmd trick
        $output; // phpmd trick

        $permissionResolver = $this->repository->getPermissionResolver();
        $user = $this->repository->getUserService()->loadUserByLogin('admin');
        $permissionResolver->setCurrentUserReference($user);
    }
}
