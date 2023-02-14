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

use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration;

return [
    'workflows' => [
        'site_configuration' => [
            'type' => 'state_machine',
            'supports' => [SiteConfiguration::class],
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'initial_marking' => SiteConfiguration::STATUS_DRAFT,
            'places' => SiteConfiguration::STATUSES,
            'transitions' => [
                SiteConfiguration::TRANS_EDIT => [
                    'from' => [SiteConfiguration::STATUS_DRAFT, SiteConfiguration::STATUS_PENDING],
                    'to' => SiteConfiguration::STATUS_DRAFT,
                ],
                SiteConfiguration::TRANS_ACTIVATE => [
                    'from' => SiteConfiguration::STATUS_DRAFT,
                    'to' => SiteConfiguration::STATUS_PENDING,
                ],
                SiteConfiguration::TRANS_START_PROCESS => [
                    'from' => SiteConfiguration::STATUS_PENDING,
                    'to' => SiteConfiguration::STATUS_PROCESSING,
                ],
                SiteConfiguration::TRANS_PROCESS => [
                    'from' => SiteConfiguration::STATUS_PROCESSING,
                    'to' => SiteConfiguration::STATUS_AVAILABLE,
                ],
                SiteConfiguration::TRANS_PUBLISH => [
                    'from' => [SiteConfiguration::STATUS_AVAILABLE, SiteConfiguration::STATUS_SUSPENDED],
                    'to' => SiteConfiguration::STATUS_READY,
                ],
                SiteConfiguration::TRANS_START_ENABLE => [
                    'from' => SiteConfiguration::STATUS_READY,
                    'to' => SiteConfiguration::STATUS_ENABLING,
                ],
                SiteConfiguration::TRANS_ENABLE => [
                    'from' => SiteConfiguration::STATUS_ENABLING,
                    'to' => SiteConfiguration::STATUS_ENABLED,
                ],
                SiteConfiguration::TRANS_UNPUBLISH => [
                    'from' => SiteConfiguration::STATUS_ENABLED,
                    'to' => SiteConfiguration::STATUS_UNSUITABLE,
                ],
                SiteConfiguration::TRANS_START_SUSPEND => [
                    'from' => SiteConfiguration::STATUS_UNSUITABLE,
                    'to' => SiteConfiguration::STATUS_SUSPENDING,
                ],
                SiteConfiguration::TRANS_SUSPEND => [
                    'from' => SiteConfiguration::STATUS_SUSPENDING,
                    'to' => SiteConfiguration::STATUS_SUSPENDED,
                ],
                SiteConfiguration::TRANS_FAIL => [
                    'from' => [
                        SiteConfiguration::STATUS_PROCESSING,
                        SiteConfiguration::STATUS_ENABLING,
                        SiteConfiguration::STATUS_SUSPENDING,
                    ],
                    'to' => SiteConfiguration::STATUS_ERROR,
                ],
            ],
        ],
    ],
];
