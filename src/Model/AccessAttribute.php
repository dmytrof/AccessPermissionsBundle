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

use Symfony\Component\Validator\Constraints as Assert;

class AccessAttribute implements \Stringable
{
    protected ?int $id = null;

    #[Assert\NotBlank]
    protected ?string $attribute = null;

    public function __construct(?string $attribute = null)
    {
        $this->setAttribute($attribute);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function setAttribute(?string $attribute): static
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->getAttribute();
    }
}
