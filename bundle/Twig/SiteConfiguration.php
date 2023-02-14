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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Twig;

use Exception;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Compose\EzRepositoryAware;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Core\Service\SiteConfiguration as SiteConfigurationService;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Compressed;

final class SiteConfiguration
{
    use EzRepositoryAware;

    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var SiteConfigurationService
     */
    private $siteConfigurationService;

    /**
     * @var Content
     */
    private $root;

    /**
     * @var Content
     */
    private $configuration;

    /**
     * @var string
     */
    private $compiledCSS;

    /**
     * @var bool
     */
    private $isEnterprise;

    public function __construct(
        ConfigResolverInterface $configResolver,
        SiteConfigurationService $siteConfigurationService,
        bool $isEnterprise
    ) {
        $this->configResolver = $configResolver;
        $this->siteConfigurationService = $siteConfigurationService;
        $this->isEnterprise = $isEnterprise;
    }

    public function getConfiguration(): Content
    {
        if (null === $this->configuration) {
            $this->configuration = $this->siteConfigurationService->getConfigurationByRootLocationId(
                $this->configResolver->getParameter('content.tree_root.location_id')
            );
        }

        return $this->configuration;
    }

    public function getRoot(): Content
    {
        if (null === $this->root) {
            $location = $this->repository->getLocationService()->loadLocation(
                $this->configResolver->getParameter('content.tree_root.location_id')
            );

            return $this->repository->getContentService()->loadContentByContentInfo($location->contentInfo);
        }

        return $this->root;
    }

    public function getCompiledCSS(): string
    {
        if (null === $this->compiledCSS) {
            $scss = new Compiler();
            $scss->setFormatter(Compressed::class);
            try {
                $this->compiledCSS = $scss->compile($this->getConfiguration()->getFieldValue('custom_scss')->text);
            } catch (Exception $e) {
                $this->compiledCSS = '';
            }
        }

        return $this->compiledCSS;
    }

    public function isEnterprise(): bool
    {
        return $this->isEnterprise;
    }
}
