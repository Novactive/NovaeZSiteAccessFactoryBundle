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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\PostLoad;
use Doctrine\ORM\Mapping\PostPersist;
use Novactive\Bundle\eZExtraBundle\Core\Helper\eZ\Content as ContentHelper;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Service\ContentTypes;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\Compose\RootLocationLinkedInterface;

final class EzRootLocationLink
{
    use Compose\EzRepositoryAware;
    use Compose\WrapperFactoryAware;

    /**
     * @var ContentHelper
     */
    private $contentHelper;

    public function __construct(ContentHelper $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    /**
     * @PostPersist
     * @PostLoad
     */
    public function postLoadHandler(RootLocationLinkedInterface $entity, LifecycleEventArgs $event): void
    {
        if (null !== $entity->getRootLocationId()) {
            $this->repository->sudo(
                function () use ($entity) {
                    $root = $this->wrapperFactory->createByLocationId($entity->getRootLocationId());
                    // A wrapper is lazy loaded, we are safe here to load everything now.
                    // and avoid permission issues later.
                    $root->content;

                    $entity->setRoot($root);
                    $result = $this->contentHelper->contentList(
                        $root->location->id,
                        [ContentTypes::SITE_CONFIGURATION],
                        [],
                        1
                    );
                    $entity->setConfiguration($result->first());
                }
            );
        }
    }
}
