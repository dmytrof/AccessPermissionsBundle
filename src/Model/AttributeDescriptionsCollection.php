<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Dmytrof\AccessPermissionsBundle\Exception\InvalidArgumentException;

class AttributeDescriptionsCollection extends ArrayCollection
{
    /**
     * AttributeDescriptionsCollection constructor.
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        parent::__construct([]);
        foreach ($elements as $element) {
            $this->add($element);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->add($value);
    }

    /**
     * {@inheritDoc}
     */
    public function add($element)
    {
        if (!$element instanceof AttributeDescription) {
            throw new InvalidArgumentException(sprintf('Element must be %s. Got: %s', AttributeDescription::class, gettype($element)));
        }
        parent::set($element->getAttribute(), $element);

        return true;
    }

    /**
     * Sorts attributes
     * @return AttributeDescriptionsCollection
     */
    public function sort(): self
    {
        $attributes = $this->toArray();
        uasort($attributes, function ($a, $b) {
            if ($a->getAttribute() == $b->getAttribute()) {
                return 0;
            }
            return ($a->getAttribute() < $b->getAttribute()) ? -1 : 1;
        });

        $this->clear();
        foreach ($attributes as $attribute) {
            $this->add($attribute);
        }
        return $this;
    }
}