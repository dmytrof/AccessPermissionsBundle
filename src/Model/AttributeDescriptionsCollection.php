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

namespace Dmytrof\AccessPermissionsBundle\Model;

use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute descriptions indexed by attribute
 *
 * @extends ArrayCollection<string, AttributeDescription>
 */
class AttributeDescriptionsCollection extends ArrayCollection
{
    /**
     * @param iterable<AttributeDescription> $elements
     */
    public function __construct(iterable $elements = [])
    {
        parent::__construct();
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * Adds the description (the key is ignored: descriptions are indexed by attribute)
     */
    public function set(string|int $key, mixed $value): void
    {
        $this->add($value);
    }

    /**
     * @throws InvalidArgumentException when the element is not AttributeDescription
     */
    public function add(mixed $element): void
    {
        if (!$element instanceof AttributeDescription) {
            throw new InvalidArgumentException(sprintf('Element must be %s. Got: %s', AttributeDescription::class, get_debug_type($element)));
        }
        parent::set($element->getAttribute(), $element);
    }

    /**
     * Sorts descriptions by attribute
     */
    public function sort(): static
    {
        $attributes = $this->toArray();
        ksort($attributes, \SORT_STRING);

        $this->clear();
        foreach ($attributes as $attribute) {
            $this->add($attribute);
        }

        return $this;
    }

    /**
     * Returns attributes descriptions as arrays
     *
     * @return array<string, array<string, ?string>>
     */
    public function getAsArray(): array
    {
        return array_map(static fn (AttributeDescription $description): array => $description->toArray(), $this->toArray());
    }
}
