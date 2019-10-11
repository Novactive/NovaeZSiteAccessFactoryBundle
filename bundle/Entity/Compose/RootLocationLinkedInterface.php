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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\Compose;

use Novactive\Bundle\eZExtraBundle\Core\Helper\eZ\Wrapper;

/**
 * Interface RootLocationLinkedInterface.
 */
interface RootLocationLinkedInterface
{
    public function getRootLocationId(): ?int;

    public function setRootLocationId(int $locationId): RootLocationLinkedInterface;

    public function getRoot(): ?Wrapper;

    public function setRoot(Wrapper $location): RootLocationLinkedInterface;

    public function getConfiguration(): ?Wrapper;

    public function setConfiguration(Wrapper $configuration): RootLocationLinkedInterface;
}
