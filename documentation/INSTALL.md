# Install

## Requirements

* Ibexa 4.2.0
* PHP PHP 7.3 || 8.0
* MariaDB 10.2 or MySQL 5.7+

## Installation steps

### Use Composer

Add the following to your `composer.json` and run `php composer.phar update novactive/ezsiteaccessfactorybundle` to refresh dependencies:

```json
"require": {
    "novactive/ezsiteaccessfactorybundle": "dev-master",
}
```

### Register the bundle

Activate the bundle in `app\AppKernel.php` file.

```php
public function registerBundles()
{
   ...
   $bundles = array(
       new FrameworkBundle(),
       ...
       new Novactive\Bundle\eZSiteAccessFactoryBundle\NovaeZSiteAccessFactoryBundle(),
   );
   ...
}
```

### Add routes

Make sure you add the routes to your routing `app/config/routing.yml`:

```yaml
_novaezsiteaccessfactory_routes:
    resource: '@NovaeZSiteAccessFactoryBundle/Resources/config/routing/main.yaml'
```

### Hook this Ibexa instance

#### Add the SiteAccess Injector in your config

Add the following file `app/config/env/siteaccesses.php`

```php
<?php
try {
    (new Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\Injector($container))();
} catch (\Exception $e) {}
```

And don't forget to include it at the top of your `app/config/ibexa.yml`

```yaml
imports:
    - { resource: env/siteaccesses.php }
```

#### Setup a writable and backuped folder for siteaccesses and cache

Add the following in your ``app/config/parameters.yml``

```yaml
novaezsiteaccessfactory_siteaccess_directory: YOURPATH
novaezsiteaccessfactory_siteaccess_cache_directory: YOURPATH/cache
novaezsiteaccessfactory_designlist: ['standard_standard']
novaezsiteaccessfactory_languages: ['eng-GB']
novaezsiteaccessfactory_default_siteaccess_groups: ['site_group1', 'site_group2', '...']
```

> Remember, your design list MUST exist
> novaezsiteaccessfactory_default_siteaccess_group : Put the list of group sites for which you want to add siteaccess created.

### Database and Content Types

If you use MariaDB, the version of MariaDB must be at least version 10.2

This bundle will add 1 table named: `novaez_siteaccess_factory_site_configuration` and will create 3 Content Types named
`novaezsiteaccessfactory_home_page`, `novaezsiteaccessfactory_static_home_page` and `novaezsiteaccessfactory_site_configuration`

```bash
bin/console novaezsiteaccessfactory:install --siteaccess=admin
composer run post-install-cmd
```

### Cronjobs

For the system to work, you need to add a new cronjob that will be in charge to manage the `site_configuration` workflow.
This is recommended to set it up to run every 30 min. 

```cron
*/30 * * * * bin/console novaezsiteaccessfactory:siteconfig:worker --siteaccess=admin
```
