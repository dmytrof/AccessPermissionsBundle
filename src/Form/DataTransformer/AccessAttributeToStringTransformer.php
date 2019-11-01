<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Form\DataTransformer;

use Dmytrof\AccessPermissionsBundle\{Exception\InvalidArgumentException,
    Model\AccessAttribute as AccessAttributeModel,
    Entity\AccessAttribute\AccessAttribute,
    Service\VotersContainer};
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;

class AccessAttributeToStringTransformer implements DataTransformerInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $entityClass = AccessAttribute::class;

    /**
     * AccessAttributeToStringTransformer constructor.
     * @param RegistryInterface $registry
     * @param VotersContainer $votersContainer
     */
    public function __construct(RegistryInterface $registry, VotersContainer $votersContainer)
    {
        $this->registry = $registry;
        $this->attributes = $votersContainer->getAllAttributes();
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * Sets class
     * @param string $entityClass
     * @return AccessAttributeToStringTransformer
     */
    public function setEntityClass(string $entityClass): self
    {
        if (!is_subclass_of($entityClass, AccessAttributeModel::class)) {
            throw new InvalidArgumentException(sprintf('Entity %s is not instance of %s', $entityClass, AccessAttributeModel::class));
        }
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (is_subclass_of($value, $this->entityClass)) {
            return (string) $value;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if ($value && in_array($value, $this->attributes)) {
            $entity = $this->registry->getRepository($this->entityClass)->findItemByAttribute($value);
            if (!$entity) {
                $class = $this->entityClass;
                $entity = new $class($value);
            }
            return $entity;
        }
        return null;
    }
}