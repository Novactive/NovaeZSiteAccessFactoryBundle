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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Listener\Workflow;

use Doctrine\ORM\EntityManagerInterface;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\Enabler;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\Processor;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration as SiteConfigurationEntity;
use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Registry;

final class SiteConfiguration
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Registry
     */
    private $workflows;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var Enabler
     */
    private $enabler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        Registry $workflows,
        Processor $processor,
        Enabler $enabler,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->workflows = $workflows;
        $this->processor = $processor;
        $this->enabler = $enabler;
        $this->logger = $logger;
    }

    public function onStartProcessCompleted(Event $event): void
    {
        /** @var SiteConfigurationEntity $entity */
        $entity = $event->getSubject();
        $this->entityManager->flush();
        try {
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_PROCESS);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_FAIL);
        }
    }

    public function onStartEnableCompleted(Event $event): void
    {
        /** @var SiteConfigurationEntity $entity */
        $entity = $event->getSubject();
        $this->entityManager->flush();
        try {
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_ENABLE);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_FAIL);
        }
    }

    public function onStartSuspendCompleted(Event $event): void
    {
        /** @var SiteConfigurationEntity $entity */
        $entity = $event->getSubject();
        $this->entityManager->flush();
        try {
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_SUSPEND);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->workflows->get($entity)->apply($entity, SiteConfigurationEntity::TRANS_FAIL);
        }
    }

    public function onProcessStarted(Event $event): void
    {
        ($this->processor)($event->getSubject());
    }

    public function onEnableStarted(Event $event): void
    {
        ($this->enabler)($event->getSubject());
    }

    public function onSuspendStarted(Event $event): void
    {
        $this->enabler->reverse($event->getSubject());
    }
}
