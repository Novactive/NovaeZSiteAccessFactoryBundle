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

use Novactive\Bundle\eZExtraBundle\Core\Helper\eZ\WrapperFactory;

trait WrapperFactoryAware
{
    /**
     * @var WrapperFactory
     */
    protected $wrapperFactory;

    /**
     * @required
     */
    public function setWrapperFactory(WrapperFactory $wrapperFactory): self
    {
        $this->wrapperFactory = $wrapperFactory;

        return $this;
    }
}
