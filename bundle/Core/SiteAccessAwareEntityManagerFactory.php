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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Core;

use Doctrine\Bundle\DoctrineBundle\Mapping\ContainerEntityListenerResolver;
use Doctrine\Persistence\ManagerRegistry as Registry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;

final class SiteAccessAwareEntityManagerFactory
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RepositoryConfigurationProvider
     */
    private $repositoryConfigurationProvider;

    /**
     * @var array
     */
    private $settings;

    /**
     * @var ContainerEntityListenerResolver
     */
    private $resolver;

    public function __construct(
        Registry $registry,
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        ContainerEntityListenerResolver $resolver,
        array $settings
    ) {
        $this->registry = $registry;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->settings = $settings;
        $this->resolver = $resolver;
    }

    private function getConnectionName(): string
    {
        $config = $this->repositoryConfigurationProvider->getRepositoryConfig();

        return $config['storage']['connection'] ?? 'default';
    }

    public function get(): EntityManagerInterface
    {
        $connectionName = $this->getConnectionName();
        // If it is the default connection then we don't bother we can directly use the default entity Manager
        if ('default' === $connectionName) {
            return $this->registry->getManager();
        }

        $connection = $this->registry->getConnection($connectionName);

        /** @var \Doctrine\DBAL\Connection $connection */
        $cache = new ArrayCache();
        $config = new Configuration();
        $config->setMetadataCacheImpl($cache);
        $driverImpl = $config->newDefaultAnnotationDriver(__DIR__.'/../Entity', false);
        $config->setMetadataDriverImpl($driverImpl);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir($this->settings['cache_dir'].'/eZSiteAccessFactoryBundle/');
        $config->setProxyNamespace('eZSiteAccessFactoryBundle\Proxies');
        $config->setAutoGenerateProxyClasses($this->settings['debug']);
        $config->setEntityListenerResolver($this->resolver);

        return EntityManager::create($connection, $config);
    }
}
