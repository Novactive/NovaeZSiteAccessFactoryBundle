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

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Workflow\Registry;

final class SiteConfigurationTransitCommand extends Command
{
    use EzRepositoryAware;

    /**
     * @var string
     */
    public static $defaultName = 'novaezsiteaccessfactory:siteconfig:transit';

    /**
     * @var Registry
     */
    private $workflows;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(?string $name = null, string $rootDir = '')
    {
        $this->rootDir = $rootDir;
        parent::__construct($name);
    }

    /**
     * @required
     */
    public function setDependencies(EntityManagerInterface $entityManager, Registry $workflows): self
    {
        $this->entityManager = $entityManager;
        $this->workflows = $workflows;

        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->addArgument('transition', InputArgument::REQUIRED, 'Transition name')
            ->setDescription('Transit the SiteConfiguration to the next step.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $transition = $input->getArgument('transition');
        $mapStatus = [
            SiteConfiguration::TRANS_START_PROCESS => SiteConfiguration::STATUS_PENDING,
            SiteConfiguration::TRANS_START_ENABLE => SiteConfiguration::STATUS_READY,
            SiteConfiguration::TRANS_START_SUSPEND => SiteConfiguration::STATUS_UNSUITABLE,
        ];

        if (!\array_key_exists($transition, $mapStatus)) {
            $io->error("Transition {$transition} is not allowed to be applied through this command.");

            return 1;
        }

        $requiredState = $mapStatus[$transition];

        $io->title("Looking for the SiteConfiguration that are '{$requiredState}'");
        $rep = $this->entityManager->getRepository(SiteConfiguration::class);
        $siteConfig = $rep->findByFilters(['status' => $requiredState]);


        $needCacheClear = false;
        foreach ($siteConfig as $config) {
            /* @var SiteConfiguration $config */
            $io->section("Doing {$config->getName()}");
            $workflow = $this->workflows->get($config);
            try {
                $workflow->apply($config, $transition);
                $needCacheClear = true;
            } catch (LogicException $exception) {
                $io->error($exception->getMessage());
            }
        }

        $this->entityManager->flush();

        if ($needCacheClear) {
            $this->clearCache($output);
            foreach ($siteConfig as $config) {
                $this->givePermissions($output, $config->getSiteaccessName());
            }
        }

        $io->success('Done.');

        return 0;
    }

    private function givePermissions(OutputInterface $output, string $siteAccessIdentifier): void
    {
        $output->write('Create Login Permissions in a new Thread...');
        $process = new Process(
            [
                "php",
                "bin/console",
                "novaezsiteaccessfactory:create:userlogin:permissions",
                "{$siteAccessIdentifier}",
            ],
            $this->rootDir
        );
        try {
            $process->mustRun();
            $output->writeln('....[OK]'.PHP_EOL.'Results:');
            $output->write($process->getOutput());
        } catch (ProcessFailedException $exception) {
            $output->write($exception->getMessage());
        }
    }

    private function clearCache(OutputInterface $output): void
    {
        $command = $this->getApplication()->find('cache:pool:clear');
        $arguments = [
            'command' => 'cache:pool:clear',
            'pools' => ['cache.global_clearer'],
        ];
        $arrayInput = new ArrayInput($arguments);
        $command->run($arrayInput, $output);

        $command = $this->getApplication()->find('cache:clear');
        $arguments = [
            'command' => 'cache:clear',
        ];
        $arrayInput = new ArrayInput($arguments);
        $command->run($arrayInput, $output);
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
