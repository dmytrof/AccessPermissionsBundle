<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute;

use Dmytrof\AccessPermissionsBundle\{Exception\AccessAttributeException, Model\AccessAttribute as AccessAttributeModel};
use Doctrine\ORM\{EntityRepository, NonUniqueResultException};

/**
 * @template T of AccessAttributeModel
 *
 * @extends EntityRepository<T>
 */
class Repository extends EntityRepository
{
    /**
     * Returns the access attribute by attribute
     *
     * @return T|null
     *
     * @throws AccessAttributeException
     */
    public function findItemByAttribute(string $attribute): ?AccessAttributeModel
    {
        $builder = $this->createQueryBuilder('aa')
            ->andWhere('aa.attribute = :attribute')
            ->setParameter('attribute', $attribute)
        ;
        try {
            return $builder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new AccessAttributeException($e->getMessage(), 0, $e);
        }
    }
}
