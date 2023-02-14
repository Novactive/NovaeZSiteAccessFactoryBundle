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

use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Worker extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'novaezsiteaccessfactory:siteconfig:worker';

    protected function configure(): void
    {
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find(SiteConfigurationTransitCommand::$defaultName);
        $transitions = [
            SiteConfiguration::TRANS_START_PROCESS,
            SiteConfiguration::TRANS_START_ENABLE,
            SiteConfiguration::TRANS_START_SUSPEND,
        ];
        foreach ($transitions as $transition) {
            $arguments = [
                'command' => SiteConfigurationTransitCommand::$defaultName,
                'transition' => $transition,
            ];
            $commandInput = new ArrayInput($arguments);
            $command->run($commandInput, $output);
        }

        return 0;
    }
}
