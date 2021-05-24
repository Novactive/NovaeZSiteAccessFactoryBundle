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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(FactoryInterface $factory, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->translator = $translator;
    }

    public function createSaveCancelMenu(): ItemInterface
    {
        $menu = $this->factory->createItem('root');
        $menu->addChild(
            'novaezsiteaccessfactory_admin_save',
            [
                'label' => $this->translator->trans('form.submit', [], 'novaezsiteaccessfactory'),
                'extras' => [
                    'icon' => 'save',
                ],
            ]
        );

        $menu->addChild(
            'novaezsiteaccessfactory_admin_cancel',
            [
                'label' => $this->translator->trans('form.cancel', [], 'novaezsiteaccessfactory'),
                'attributes' => ['class' => 'btn-danger'],
                'extras' => [
                    'icon' => 'circle-close',
                ],
            ]
        );

        return $menu;
    }
}
