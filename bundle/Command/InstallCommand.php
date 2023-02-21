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

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Migration\MigrationService;
use Ibexa\Migration\Repository\Migration;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\SiteAccessRegistryAware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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

    /** @var ContentTypeService */
    private $contentTypeService;

    /**
     * @required
     */
    public function setDependencies(
        Environment $twig,
        MigrationService $migrationService,
        ContentTypeService $contentTypeService
    ): void {
        $this->twig = $twig;
        $this->migrationService = $migrationService;
        $this->contentTypeService = $contentTypeService;
    }

    protected function configure(): void
    {
        $this
            ->setName('novaezsiteaccessfactory:install')
            ->setDescription('Install what necessary in the DB.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Update the Database with Custom Almavia CX eZ Site Access Factory Table.');
        $command = $this->getApplication()->find('doctrine:schema:update');
        $arguments = [
            'command' => 'doctrine:schema:update',
            '--dump-sql' => true,
            '--force' => true,
        ];
        $arrayInput = new ArrayInput($arguments);
        $command->run($arrayInput, $output);

        $io->title('Update the Content Repository to create Custom Almavia CX eZ Site Access Factory Content Types.');

        try {
            $this->contentTypeService->loadContentTypeByIdentifier('novaezsiteaccessfactory_home_page');
        } catch (\Exception $e) {
            $content = $this->twig->render('@ibexadesign/site_configuration/content_types.yaml.twig');
            $migrationName = 'siteaccess_factory_data.yaml';
            $migration = new Migration($migrationName, $content);
            $this->migrationService->add($migration);
            if (!$this->migrationService->isMigrationExecuted($migration)) {
                $this->migrationService->executeOne($migration);
            }
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
