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

namespace Novactive\Bundle\eZSiteAccessFactoryBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Novactive\Bundle\eZSiteAccessFactoryBundle\Entity\SiteConfiguration as SiteConfigurationEntity;

class SiteConfiguration extends EntityRepository
{
    protected function getAlias(): string
    {
        return 'sc';
    }

    protected function getEntityClass(): string
    {
        return SiteConfigurationEntity::class;
    }

    protected function createQBForFilters(array $filters = []): QueryBuilder
    {
        $qb = parent::createQBForFilters($filters);
        if (isset($filters['status'])) {
            $qb->andWhere($qb->expr()->in($this->getAlias().'.lastStatus', ':statuses'))->setParameter(
                'statuses',
                $filters['status']
            );
        }

        return $qb;
    }

    public function fetchStatusesData(array $filters = []): array
    {
        unset($filters['status']);
        $total = 0;
        foreach (SiteConfigurationEntity::STATUSES as $status) {
            $statuses[$status] = $this->countByFilters($filters + ['status' => $status]);

            $total += $statuses[$status];
        }

        return ['count' => $total, 'results' => $statuses ?? []];
    }
}
