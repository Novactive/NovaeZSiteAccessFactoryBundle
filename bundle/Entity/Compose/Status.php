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

trait Status
{
    /**
     * @var string
     * @ORM\Column(name="OBJ_last_status", type="string", length=255, nullable=false)
     */
    private $lastStatus;

    /**
     * @var array
     * @ORM\Column(name="OBJ_statuses", type="array", nullable=false)
     */
    private $statuses;

    public function getLastStatus(): string
    {
        return $this->lastStatus;
    }

    public function setLastStatus(string $lastStatus): self
    {
        if ($this->lastStatus === $lastStatus) {
            return $this;
        }
        $this->lastStatus = $lastStatus;
        $this->statuses[] = [$lastStatus, new DateTime()];

        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }
}
