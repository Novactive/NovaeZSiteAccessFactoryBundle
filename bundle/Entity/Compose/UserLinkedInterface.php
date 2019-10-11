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
 * Interface UserLinkedInterface.
 */
interface UserLinkedInterface
{
    public function getUserId(): int;

    public function setUserId(int $userId): UserLinkedInterface;

    public function getUser(): Wrapper;

    public function setUser(Wrapper $user): UserLinkedInterface;
}
