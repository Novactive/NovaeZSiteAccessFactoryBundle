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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Listener;

use eZ\Publish\API\Repository\PermissionResolver;
use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Knp\Menu\MenuItem;

final class AdminTopMenu
{
    /**
     * @var PermissionResolver
     */
    private $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    public function onMenuConfigure(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $isAdmin = $this->permissionResolver->hasAccess('setup', 'administrate');
        $children = $menu->getChildren();

        /** @var MenuItem $item */
        $item = $children['main__content'];
        $item->setLabel('Tableau de bord');

        if ($isAdmin) {
            $top = $menu->addChild(
                'novaezsiteaccessfactoryadmin',
                [
                    'label' => 'Nova eZ SiteAccess Factory Admin',
                ]
            )
                ->setExtra('icon', 'sites');
            $top->addChild(
                'novaezsiteaccessfactoryadmin_websites_management',
                [
                    'label' => 'Site Management',
                    'route' => 'novaezsiteaccessfactoryadmin_siteconfiguration_index',
                ]
            );
        }
    }
}
