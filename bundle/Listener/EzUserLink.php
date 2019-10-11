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
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\Compose\UserLinkedInterface;

final class EzUserLink
{
    use Compose\EzRepositoryAware;
    use Compose\WrapperFactoryAware;

    /**
     * @PostPersist
     * @PostLoad
     */
    public function postLoadHandler(UserLinkedInterface $entity, LifecycleEventArgs $event): void
    {
        if (null !== $entity->getUserId()) {
            $user = $this->repository->sudo(
                function () use ($entity) {
                    $wrapper = $this->wrapperFactory->createByContentId($entity->getUserId());
                    // A wrapper is lazy loaded, we are safe here to load everything now.
                    // and avoid permission issues later.
                    $wrapper->content;
                    $wrapper->location;

                    return $wrapper;
                }
            );
            $entity->setUser($user);
        }
    }
}
