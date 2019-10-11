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

use Kaliop\eZMigrationBundle\API\Collection\MigrationDefinitionCollection;
use Kaliop\eZMigrationBundle\Core\MigrationService;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\SiteAccessRegistryAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class InstallCommand extends Command
{
    use SiteAccessRegistryAware;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var MigrationService
     */
    private $migrationService;

    /**
     * @required
     */
    public function setDependencies(Environment $twig, MigrationService $migrationService): void
    {
        $this->twig = $twig;
        $this->migrationService = $migrationService;
    }

    protected function configure(): void
    {
        $this
            ->setName('novaezsiteaccessfactory:install')
            ->setDescription('Install what necessary in the DB.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Update the Database with Custom Novactive eZ Site Access Factory Table.');
        $command = $this->getApplication()->find('doctrine:schema:update');
        $arguments = [
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--force' => true,
        ];
        $arrayInput = new ArrayInput($arguments);
        $command->run($arrayInput, $output);

        $io->title('Update the Content Repository to create Custom Novactive eZ Site Access Factory Content Types.');

        $content = $this->twig->render('@ezdesign/site_configuration/content_types.yaml.twig');
        $fs = new Filesystem();
        $path = "{$this->siteAccessCacheDirectory}/".time().'_data.yaml';
        $fs->dumpFile($path, $content);
        /** @var MigrationDefinitionCollection $migrationDefinitions */
        $migrationDefinitions = $this->migrationService->getMigrationsDefinitions([$path]);
        foreach ($migrationDefinitions as $migrationDefinition) {
            $migration = $this->migrationService->parseMigrationDefinition($migrationDefinition);
            $this->migrationService->executeMigration($migration);
        }

        $io->success('Done.');
    }
}
