# Install

## Requirements

* eZ Platform 2.x
* PHP PHP 7.3

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

### Database and Content Types

This bundle will add 1 table named: `novaez_siteaccess_factory_site_configuration` and will create 2 Content Types named
`novaezsiteaccessfactory_home_page` and `novaezsiteaccessfactory_site_configuration`

```bash
bin/console novaezsiteaccessfactory:install --siteaccess=admin
```

### Hook this eZ instance

#### Add the SiteAccess Injector in your config

Add the following file `app/config/env/siteaccess.php`

```php
<?php
try {
    (new Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccess\Injector($container))();
} catch (\Exception $e) {}
```

And don't forget to include it at the top of your `app/config/ezplatform.yml`

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
```

#### Cronjobs

For the system to work, you need to add a new cronjob that will be in charge to manage the `site_configuration` workflow.
This is recommended to set it up to run every 30 min. 

```cron
*/30 * * * * bin/console novaezsiteaccessfactory:siteconfig:worker --siteaccess=admin
```
