<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Dmytrof\AccessPermissionsBundle\{
    Model\AccessAttribute as Model, Entity\AccessAttribute\Repository
};

/**
 * @ORM\Table(name="dmytrof_access_attribute")
 * @ORM\Entity(repositoryClass=Repository::class)
 * @UniqueEntity(fields={"attribute"})
 */
class AccessAttribute extends Model
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    protected $attribute;
}