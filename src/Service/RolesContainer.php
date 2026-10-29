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

namespace Dmytrof\AccessPermissionsBundle\Service;

use Dmytrof\AccessPermissionsBundle\Exception\RoleException;
use Doctrine\Inflector\{Inflector, NoopWordInflector};

/**
 * Roles of security role hierarchy (and added ones) used by voters' "can-methods"
 *
 * @implements \IteratorAggregate<int, string>
 */
class RolesContainer implements \IteratorAggregate, \Countable
{
    public const ROLE_PREFIX = 'ROLE_';

    /**
     * @var list<string>
     */
    protected array $roles = [];

    private ?Inflector $inflector = null;

    /**
     * @param array<string, list<string>> $systemRoles role hierarchy: role => inherited roles
     */
    public function __construct(array $systemRoles = [])
    {
        $roles = [];
        foreach ($systemRoles as $role => $inheritedRoles) {
            array_push($roles, $role, ...$inheritedRoles);
        }
        $this->roles = array_values(array_unique($roles));
    }

    /**
     * @return \ArrayIterator<int, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->roles);
    }

    public function count(): int
    {
        return count($this->roles);
    }

    /**
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * Returns regex pattern of classified roles
     */
    public function getRolesPattern(): string
    {
        return implode('|', array_map($this->classifyRole(...), $this->getRoles()));
    }

    /**
     * Adds roles
     *
     * @throws RoleException when role does not begin with "ROLE_"
     */
    public function addRole(string ...$roles): static
    {
        foreach ($roles as $role) {
            if (!str_starts_with($role, self::ROLE_PREFIX)) {
                throw new RoleException(sprintf('Role must begin with "%s". Input was: %s', self::ROLE_PREFIX, $role));
            }
            if (!$this->hasRole($role)) {
                $this->roles[] = $role;
            }
        }

        return $this;
    }

    /**
     * Removes roles
     */
    public function removeRole(string ...$roles): static
    {
        $this->roles = array_values(array_diff($this->roles, $roles));

        return $this;
    }

    /**
     * Classifies role (Converts ROLE_ADMIN to RoleAdmin)
     */
    public function classifyRole(string $role): string
    {
        return $this->getInflector()->classify(strtolower($role));
    }

    /**
     * Converts RoleAdmin to ROLE_ADMIN
     */
    public function declassifyRole(string $classifiedRole): string
    {
        return strtoupper($this->getInflector()->tableize($classifiedRole));
    }

    private function getInflector(): Inflector
    {
        return $this->inflector ??= new Inflector(new NoopWordInflector(), new NoopWordInflector());
    }
}
