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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class Injector
{
    use SiteAccessRegistryAware;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var bool
     */
    private $isEnterprise;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));
        $this->isEnterprise = \in_array('EzPlatformPageFieldTypeBundle', $activatedBundles, true);

        $this->setSiteAccessDirectory($container->getParameter('novaezsiteaccessfactory_siteaccess_directory'));
        $this->setSiteAccessCacheDirectory(
            $container->getParameter('novaezsiteaccessfactory_siteaccess_cache_directory')
        );
    }

    private function load(array $data, string $fileName): void
    {
        $fs = new Filesystem();
        $fs->dumpFile("{$this->siteAccessCacheDirectory}/{$fileName}.yaml", Yaml::dump($data, 8));
        $loader = new Loader\YamlFileLoader($this->container, new FileLocator($this->siteAccessCacheDirectory));
        $loader->load("{$fileName}.yaml");
    }

    public function __invoke()
    {
        $fs = new Filesystem();
        $fs->remove($this->siteAccessCacheDirectory);
        $fs->mkdir($this->siteAccessCacheDirectory);

        $finder = new Finder();
        $finder->in($this->siteAccessDirectory)->files()->name('*.json');

        $systemConfigs = [];
        foreach ($finder as $file) {
            $systemConfigs[$file->getBasename('.json')] = json_decode(file_get_contents($file->getRealPath()));
        }

        $list = $groups = $pageBuilder = [];
        foreach ($systemConfigs as $key => $config) {
            $system = $siteAccess = [];
            $system['design'] = $config->design;
            $system['cache_service_name'] = $config->cache_service_name;
            $system['languages'] = $config->languages;
            $system['content']['tree_root']['location_id'] = $config->root_location_id ?? 2;
            $siteAccess['ibexa']['system'][$key] = $system;

            $this->load($siteAccess, $key);
            $list[] = $key;

            foreach ($config->groups as $group) {
                $groups[$group][] = $key;
            }

            if ($this->isEnterprise) {
                if (null != $config->page_builder) {
                    foreach ($config->page_builder as $builder) {
                        $pageBuilder[$builder][] = $key;
                    }
                }
            }
        }

        $siteAccessConfigs['ibexa']['siteaccess']['list'] = $list;
        $siteAccessConfigs['ibexa']['siteaccess']['groups'] = $groups;

        // Match site by URI
        foreach ($list as $key => $value) {
            $siteAccessConfigs['ibexa']['siteaccess']['match']['Map\URI'][$value] = $value;
        }

        if ($this->isEnterprise) {
            foreach ($pageBuilder as $adminKey => $keys) {
                foreach ($keys as $key) {
                    $siteAccessConfigs['ibexa']['system'][$adminKey]['page_builder']['siteaccess_list'][] = $key;
                }
            }
        }
        $this->load($siteAccessConfigs, 'global');
    }
}
