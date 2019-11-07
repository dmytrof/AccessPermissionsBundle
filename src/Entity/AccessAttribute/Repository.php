<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute;

use Doctrine\ORM\{EntityRepository, NonUniqueResultException};
use Dmytrof\AccessPermissionsBundle\{Exception\AccessAttributeException, Model\AccessAttribute as AccessAttributeModel};

class Repository extends EntityRepository
{
    /**
     * Returns the access attribute by attribute
     * @param string $attribute
     * @return AccessAttributeModel|null
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
            throw new AccessAttributeException($e->getMessage());
        }
    }
}