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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class NovaeZSiteAccessFactoryExtension extends Extension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'nova_ezsiteaccess_factory';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.yaml');
        $loader->load('ezadminui.yaml');
        $loader->load('listeners.yaml');

        // Enable this bundle for Twig
        $asseticBundles = $container->getParameter('assetic.bundles');
        $asseticBundles[] = 'NovaeZSiteAccessFactoryBundle';
        $container->setParameter('assetic.bundles', $asseticBundles);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $twigConfigFile = __DIR__.'/../Resources/config/twig.yaml';
        $config = Yaml::parse(file_get_contents($twigConfigFile));
        $container->prependExtensionConfig('twig', $config);
        $container->addResource(new FileResource($twigConfigFile));

        $workflowFile = __DIR__.'/../Resources/config/workflows.php';
        $config = include $workflowFile;
        $container->prependExtensionConfig('framework', $config);
        $container->addResource(new FileResource($workflowFile));
        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        $isEnterprise = \in_array('EzPlatformPageFieldTypeBundle', $activatedBundles, true);
        $container->setParameter('novaezsiteaccessfactory.ezplatform.is_enterprise', $isEnterprise);

        $themesConfigFile = __DIR__.'/../Resources/config/themes.yaml';
        $config = Yaml::parse(file_get_contents($themesConfigFile));
        $container->prependExtensionConfig('ezdesign', $config);
        $container->addResource(new FileResource($themesConfigFile));
    }
}
