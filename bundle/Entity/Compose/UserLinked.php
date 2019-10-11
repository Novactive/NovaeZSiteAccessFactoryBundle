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
 * Trait UserLinked.
 */
trait UserLinked
{
    /**
     * @ORM\Column(name="OBJ_user_id", type="integer", nullable=false)
     *
     * @var int
     */
    private $userId;

    /**
     * @var Wrapper
     */
    private $user;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): UserLinkedInterface
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUser(): Wrapper
    {
        return $this->user;
    }

    public function setUser(Wrapper $user): UserLinkedInterface
    {
        $this->user = $user;
        $this->userId = $user->content->id;

        return $this;
    }
}
