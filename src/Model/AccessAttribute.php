<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AccessAttribute
{
    /**
     * ID
     * @var integer
     */
    protected $id;

    /**
     * Attribute
     * @var string
     *
     * @Assert\NotBlank
     */
    protected $attribute;

    /**
     * AccessAttribute constructor.
     * @param string|null $attribute
     */
    public function __construct(string $attribute = null)
    {
        $this->setAttribute($attribute);
    }

    /**
     * Returns id
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Sets id
     * @param int $id
     * @return AccessAttribute
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns attribute
     * @return null|string
     */
    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    /**
     * Sets attribute
     * @param null|string $attribute
     * @return AccessAttribute
     */
    public function setAttribute(?string $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function __toString()
    {
        return (string) $this->getAttribute();
    }
}