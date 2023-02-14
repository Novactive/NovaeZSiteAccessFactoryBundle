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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

abstract class EntityRepository extends BaseEntityRepository
{
    abstract protected function getAlias(): string;

    abstract protected function getEntityClass(): string;

    protected function createQBForFilters(array $filters = []): QueryBuilder
    {
        $qb = $this->createQB();
        if (isset($filters['attr_map'])) {
            $index = 0;
            foreach ($filters['attr_map'] as $key => $value) {
                $field = $this->getAlias().'.'.$key;
                if (null === $value) {
                    $qb->andWhere($qb->expr()->isNull($field));
                } elseif ('notNull' === $value) {
                    $qb->andWhere($qb->expr()->isNotNull($field));
                } elseif (\is_array($value)) {
                    $qb->andWhere($qb->expr()->in($field, $value));
                } else {
                    $qb->andWhere($qb->expr()->eq($field, ':value'.$index))->setParameter(
                        'value'.$index,
                        $value
                    );
                }
                ++$index;
            }
        }

        return $qb;
    }

    protected function createQB(): QueryBuilder
    {
        return $this->createQueryBuilder($this->getAlias())->select($this->getAlias())->distinct();
    }

    /**
     * @return array|ArrayCollection
     */
    public function findByAttributes(array $attributesMap, array $filters = [])
    {
        $filters['attr_map'] = $attributesMap;

        $qb = $this->createQBForFilters($filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array|ArrayCollection
     */
    public function findByFilters(array $filters = [])
    {
        $qb = $this->createQBForFilters($filters);

        return $qb->getQuery()->getResult();
    }

    public function countByFilters(array $filters = []): int
    {
        $qb = $this->createQBForFilters($filters);
        $qb->select($qb->expr()->countDistinct($this->getAlias().'.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getPagerFilters(array $filters = [], int $page = 1, int $limit = 25): Pagerfanta
    {
        $adapter = new DoctrineORMAdapter($this->createQBForFilters($filters));
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $pager;
    }
}
