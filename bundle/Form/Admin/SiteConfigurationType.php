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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Form\Admin;

use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteConfigurationType extends AbstractType
{
    /**
     * @var array
     */
    private $typeList;

    /**
     * @var array
     */
    private $themeList;

    /**
     * @var array
     */
    private $languageList;

    public function __construct(array $designList = [], array $languageList = [])
    {
        foreach ($designList as $item) {
            list($theme, $type) = explode('_', $item);
            $this->typeList[ucfirst($type)] = $type;
            $this->themeList[ucfirst($theme)] = $theme;
        }

        $this->languageList = array_combine($languageList, $languageList);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'site.configuration.form.name',
                ]
            )
            ->add(
                'siteaccessName',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'site.configuration.form.siteaccessname.name',
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => 'site.configuration.form.type',
                    'choices' => $this->typeList,
                ]
            )
            ->add(
                'theme',
                ChoiceType::class,
                [
                    'label' => 'site.configuration.form.theme',
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => $this->themeList,
                ]
            )
            ->add(
                'template',
                ChoiceType::class,
                [
                    'choices' => [
                        'site.configuration.form.model.one' => true,
                        'site.configuration.form.model.two' => false,
                    ],
                ]
            )
            ->add(
                'prioritizedLanguges',
                TextType::class,
                [
                    'label' => 'site.configuration.form.languages',
                    'required' => true,
                ]
            )
            ->add(
                'adminEmail',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'site.configuration.form.admin.email',
                ]
            );

        $builder->get('prioritizedLanguges')->addModelTransformer(
            new CallbackTransformer(
                function (?array $array) {
                    return implode(',', $array ?? []);
                },
                function (?string $string) {
                    return array_map(
                        function ($item) {
                            return trim($item);
                        },
                        explode(',', $string ?? '')
                    );
                }
            )
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var SiteConfiguration $config */
                $config = $event->getData();
                $form = $event->getForm();

                if (SiteConfiguration::STATUS_DRAFT != $config->getLastStatus()) {
                    // Once created the siteaccessName is readonly
                    $form->remove('siteaccessName');
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SiteConfiguration::class,
                'translation_domain' => 'novaezsiteaccessfactory',
            ]
        );
    }
}
