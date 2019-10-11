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

use DateTime;
use Doctrine\ORM\Mapping as ORM;

trait Metadata
{
    /**
     * @var DateTime
     * @ORM\Column(name="OBJ_created", type="datetime")
     */
    private $created;

    /**
     * @var DateTime
     * @ORM\Column(name="OBJ_updated", type="datetime")
     */
    private $updated;

    public function getCreated(): DateTime
    {
        return $this->created ?? new DateTime();
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        $this->setUpdated($created);

        return $this;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated ?? new DateTime();
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }
}
