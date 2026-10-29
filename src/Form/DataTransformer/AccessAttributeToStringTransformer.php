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

namespace Dmytrof\AccessPermissionsBundle\Form\DataTransformer;

use Dmytrof\AccessPermissionsBundle\{Entity\AccessAttribute\AccessAttribute, Exception\InvalidArgumentException, Model\AccessAttribute as AccessAttributeModel, Service\VotersContainer};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\{DataTransformerInterface, Exception\TransformationFailedException};

/**
 * Transforms access attribute entity to attribute string and back (finds existing entity or creates a new one)
 *
 * @implements DataTransformerInterface<AccessAttributeModel, AccessAttributeModel|string>
 */
class AccessAttributeToStringTransformer implements DataTransformerInterface
{
    /**
     * @var array<string, string>
     */
    protected array $attributes;

    /**
     * @var class-string<AccessAttributeModel>
     */
    protected string $entityClass = AccessAttribute::class;

    public function __construct(
        protected ManagerRegistry $registry,
        VotersContainer $votersContainer,
    ) {
        $this->attributes = $votersContainer->getAllAttributes();
    }

    /**
     * @return class-string<AccessAttributeModel>
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * Sets entity class
     *
     * @throws InvalidArgumentException when the class does not extend AccessAttribute model
     */
    public function setEntityClass(string $entityClass): static
    {
        if (!is_a($entityClass, AccessAttributeModel::class, true)) {
            throw new InvalidArgumentException(sprintf('Entity %s is not instance of %s', $entityClass, AccessAttributeModel::class));
        }
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Returns all access attributes
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function transform(mixed $value): mixed
    {
        return $value instanceof $this->entityClass ? $value : null;
    }

    public function reverseTransform(mixed $value): mixed
    {
        if ($value instanceof $this->entityClass) {
            $value = (string) $value;
        }
        if (null === $value || '' === $value) {
            return null;
        }
        if (!is_string($value) || !in_array($value, $this->getAttributes(), true)) {
            $value = is_scalar($value) ? (string) $value : get_debug_type($value);
            $failure = new TransformationFailedException(sprintf('Undefined access attribute %s', $value));
            $failure->setInvalidMessage('The given "{{ value }}" value is not a valid access attribute.', [
                '{{ value }}' => $value,
            ]);
            throw $failure;
        }

        $repository = $this->registry->getRepository($this->entityClass);
        $entity = method_exists($repository, 'findItemByAttribute')
            ? $repository->findItemByAttribute($value)
            : $repository->findOneBy(['attribute' => $value]);

        return $entity ?? new ($this->entityClass)($value);
    }
}
