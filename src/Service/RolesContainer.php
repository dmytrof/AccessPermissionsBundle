<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Service;

use Doctrine\Common\Inflector\Inflector;
use Dmytrof\AccessPermissionsBundle\Exception\RoleException;

class RolesContainer implements \IteratorAggregate
{
    /**
     * @var array|string[]
     */
	protected $roles;

    /**
     * RolesContainer constructor.
     * @param array $systemRoles
     */
    public function __construct(array $systemRoles)
    {
        $roles = [];
        foreach ($systemRoles as $role => $inheritedRoles) {
            array_push($roles, $role, ...$inheritedRoles);
        }
        $this->roles = array_unique($roles);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->roles);
    }

    /**
     * Returns roles
     * @return array
     */
    public function getRoles(): array
    {
        return (array) $this->roles;
    }

    /**
     * returns roles pattern
     * @return string
     */
    public function getRolesPattern(): string
    {
        return join('|', array_map([$this, 'classifyRole'], $this->getRoles()));
    }

    /**
     * Adds role
     * @param string $role
     * @return $this
     */
    public function addRole(string $role): self
    {
        foreach (func_get_args() as $role) {
            if (!substr($role, 0,5) === 'ROLE_') {
                throw new RoleException(sprintf('Role must begin with "ROLE_"'));
            }
            if (!in_array($role, $this->roles)) {
                array_push($this->roles, $role);
            }
        }

        return $this;
    }

    /**
     * Removes role
     * @param string $role
     * @return $this
     */
    public function removeRole(string $role): self
    {
        $this->roles = array_diff($this->roles, func_get_args());
        return $this;
    }

    /**
     * Classifies role (Converts ROLE_ADMIN to RoleAdmin)
     * @param string $role
     * @return string
     */
    public function classifyRole(string $role): string
    {
        return Inflector::classify(strtolower($role));
    }

    /**
     * Converts RoleAdmin to ROLE_ADMIN
     * @param string $classifiedRole
     * @return string
     */
    public function declassifyRole(string $classifiedRole): string
    {
        return strtoupper(Inflector::tableize($classifiedRole));
    }
}