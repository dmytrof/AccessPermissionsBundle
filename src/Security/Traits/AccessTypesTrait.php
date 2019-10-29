<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Security\Traits;

use Dmytrof\AccessPermissionsBundle\{Exception\RuntimeException, Security\AbstractVoter, Security\AccessTypesInterface};

trait AccessTypesTrait
{
    /**
     * @inheritDoc
     * @see AccessTypesInterface::getAccessTypesPattern()
     */
    public static function getAccessTypesPattern(): string
    {
        self::_checkAccessTypesRequirements();
        return join('|', array_map('ucfirst', static::getAccessTypes()));
    }

    /**
     * @inheritDoc
     * @see AccessTypesInterface::getAccessTypes()
     */
    public static function getAccessTypes(): array
    {
        self::_checkAccessTypesRequirements();
        return static::getConstant('ACCESS_TYPES');
    }

    /**
     * @inheritDoc
     * @see AccessTypesInterface::getAccessTypes()
     */
    public static function getAccessTypesWeights(): array
    {
        self::_checkAccessTypesRequirements();
        return static::getConstant('ACCESS_TYPES_WEIGHTS');
    }


    /**
     * @inheritDoc
     * @see AccessTypesInterface::getAccessTypesWeights()
     */
    public static function getAccessTypeRoles(string $accessType): ?array
    {
        self::_checkAccessTypesRequirements();
        $roles = static::getConstant('ACCESS_TYPES_ROLES');
        if ($accessType && isset($roles[$accessType])) {
            return (array) $roles[$accessType];
        }
        return null;
    }

    /**
     * Checks requirements for trait usage
     */
    private static function _checkAccessTypesRequirements(): void
    {
        if (!is_subclass_of(static::class, AbstractVoter::class) ) {
            throw new RuntimeException(sprintf('%s is not extends %s interface', static::class, AbstractVoter::class));
        }
    }
}