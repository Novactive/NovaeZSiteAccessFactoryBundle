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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Service;

use DateTime;
use eZ\Publish\API\Repository\Values\Content\Content;
use Novactive\Bundle\eZExtraBundle\Core\Helper\eZ\Content as ContentHelper;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration as SiteConfigurationEntity;

final class SiteConfiguration
{
    /**
     * @var ContentHelper
     */
    private $contentHelper;

    public function __construct(ContentHelper $contentHelper)
    {
        $this->contentHelper = $contentHelper;
    }

    public function getRoot(SiteConfigurationEntity $configuration): Content
    {
        return $configuration->getRoot()->content;
    }

    public function getConfiguration(SiteConfigurationEntity $configuration): Content
    {
        return $this->getConfigurationByRootLocationId($configuration->getRootLocationId());
    }

    public function duplicate(SiteConfigurationEntity $configuration, string $suffix = 'copy'): SiteConfigurationEntity
    {
        $new = new SiteConfigurationEntity();
        $new->setName($configuration->getName().'_'.$suffix);
        $new->setSiteaccessName($configuration->getSiteaccessName().'_'.$suffix);
        $new->setLastStatus(SiteConfigurationEntity::STATUS_DRAFT);
        $new->setPrioritizedLanguges($configuration->getPrioritizedLanguges());
        $new->setType($configuration->getType());
        $new->setTheme($configuration->getTheme());
        $new->setAdminEmail($configuration->getAdminEmail());
        $new->setCreated(new Datetime());
        $new->setGroups($configuration->getGroups());
        $new->setTemplate($configuration->getTemplate());

        return $new;
    }

    public function getConfigurationByRootLocationId(int $rootLocationId): Content
    {
        $result = $this->contentHelper->contentList(
            $rootLocationId,
            [ContentTypes::SITE_CONFIGURATION],
            [],
            1
        );

        return $result->first()->content;
    }
}
