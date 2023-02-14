# Usage

## Description

For now this bundle allows you to configure some properties of a Site Access.

- **design**: so the template for that Site Access will be found in your existing Designs.
   - Select a **type** and a **theme**, those 2 will be joined with `_` to define the `design`
   - Example: `event` `dark` will setup the design `event_dark` for this Site Access. 
- **languages**: you can setup the prioritize list of languages
- **modele**: select content type for home page.

> The list of Design must be available.

By default the new Site Access will be put in the default `site_group`

> that will change soon, so you will have the ability to configure it from the Admin UI. 

## Configuration

There are 4 configuration parameters:

- **novaezsiteaccessfactory_siteaccess_directory**: A path, writable and backuped to save the Site Access configuration in JSON.
- **novaezsiteaccessfactory_siteaccess_cache_directory**: A path, writable and backuped to save the Site Access configuration cached in JSON.
- **novaezsiteaccessfactory_designlist**: The list of available Design in your project (or that you want to enable for the factory).
- **novaezsiteaccessfactory_languages**: The list of available Languages in your project (or that you want to enable for the factory).
- **novaezsiteaccessfactory_default_siteaccess_groups**: The list of group sites for which you want to add siteaccess created.


## Use Case

We implemented this bundle in an Ibexa (multi-site) instance.

- in the file app/config/ibexa.yml

```yaml
ezpublish:
    system:
        site_group:
            content_view:
                full:
                   landing_page:
                       template: 'themes/siteaccess_factory/location/full/content_page_empty.html.twig'
                       match:
                           Identifier\ContentType: novaezsiteaccessfactory_static_home_page
```

- That means, you need to create the content_page_empty.html.twig file. Here is an example of the contents of this file

```twig
{% if content.getField('content_html') and not ez_is_field_empty(content, "content_html") %}
    {{ ez_field_value(content, "content_html") | raw }}
{% else %}
    <h2>Your Texte ......</h2>
{% endif %}

```

- We have added the configuration in the app/config/services.yml. it concerns the multi-database
```yaml
services:
    # default configuration for services in *this* file
    _defaults:
        autowire: true
        autoconfigure: true
    # Redefine the service here from "ezplatform/vendor/novactive/ezsiteaccessfactorybundles/bundle/Resources/config/services.yml"
    # Because with more than one EM we needed to manually set the default listener
    Novactive\Bundle\eZSiteAccessFactoryBundle\Core\SiteAccessAwareEntityManagerFactory:
        $settings: { debug: "%kernel.debug%", cache_dir: "%kernel.cache_dir%" }
        $resolver: '@doctrine.orm.default_entity_listener_resolver'
```

