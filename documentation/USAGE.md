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

