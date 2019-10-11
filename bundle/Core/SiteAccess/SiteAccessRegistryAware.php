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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess;

use Symfony\Component\Filesystem\Filesystem;

trait SiteAccessRegistryAware
{
    /**
     * @var string
     */
    private $siteAccessDirectory;

    /**
     * @var string
     */
    private $siteAccessCacheDirectory;

    /**
     * @required
     */
    public function setSiteAccessDirectory(string $siteAccessDirectory): void
    {
        $fs = new Filesystem();
        $this->siteAccessDirectory = $siteAccessDirectory;
        if (!$fs->exists($this->siteAccessDirectory)) {
            $fs->mkdir($this->siteAccessDirectory);
        }
    }

    /**
     * @required
     */
    public function setSiteAccessCacheDirectory(string $siteAccessCacheDirectory): void
    {
        $fs = new Filesystem();
        $this->siteAccessCacheDirectory = $siteAccessCacheDirectory;
        if (!$fs->exists($this->siteAccessCacheDirectory)) {
            $fs->mkdir($this->siteAccessCacheDirectory);
        }
    }
}
