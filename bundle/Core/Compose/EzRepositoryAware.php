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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose;

use eZ\Publish\API\Repository\Repository;

trait EzRepositoryAware
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @required
     */
    public function setRepository(Repository $repository): self
    {
        $this->repository = $repository;

        return $this;
    }
}
