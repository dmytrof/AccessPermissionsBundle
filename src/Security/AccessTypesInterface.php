<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Security;

interface AccessTypesInterface
{
    public const ACCESS_TYPE_SUPER_ADMIN  = 'superAdmin';
    public const ACCESS_TYPE_GUEST        = 'guest';

    /**
     * Returns access types
     * @return array
     */
    public static function getAccessTypes(): array;

    /**
     * Returns patters for access types
     * @return string
     */
    public static function getAccessTypesPattern(): string;

    /**
     * Returns access roles
     * @param string $accessType
     * @return array|null
     */
    public static function getAccessTypeRoles(string $accessType): ?array;
}