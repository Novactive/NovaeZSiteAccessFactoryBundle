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

trait RootLocationLinked
{
    /**
     * @var int
     *
     * @ORM\Column(name="OBJ_root_location_id", type="integer", nullable=true)
     */
    private $rootLocationId;

    /**
     * @var Wrapper
     */
    private $root;

    /**
     * @var Wrapper
     */
    private $configuration;

    public function getRootLocationId(): ?int
    {
        return $this->rootLocationId;
    }

    public function setRootLocationId(int $locationId): RootLocationLinkedInterface
    {
        $this->rootLocationId = $locationId;

        return $this;
    }

    public function getRoot(): ?Wrapper
    {
        return $this->root;
    }

    public function setRoot(Wrapper $root): RootLocationLinkedInterface
    {
        $this->root = $root;
        $this->rootLocationId = $root->location->id;

        return $this;
    }

    public function getConfiguration(): ?Wrapper
    {
        return $this->configuration;
    }

    public function setConfiguration(Wrapper $configuration): RootLocationLinkedInterface
    {
        $this->configuration = $configuration;

        return $this;
    }
}
