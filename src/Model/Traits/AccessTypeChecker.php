<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Model\Traits;

use Dmytrof\AccessPermissionsBundle\Security\{AbstractVoter, RolesInterface};

trait AccessTypeChecker
{
    /**
     * @var array
     */
    protected static $_accessTypes = [];

    /**
     * @var array
     */
    protected static $_accessTypeChecks = [];

    /**
     * @return array
     */
    protected static function getAccessTypesToCheck(): array
    {
        return AbstractVoter::getAccessTypes();
    }

    /**
     * Returns access type with superiors
     * @param string $minimalAccessType
     * @return array
     */
    public static function getAccessTypeWithSuperiors(string $minimalAccessType)
    {
        if (!isset(static::$_accessTypes[$minimalAccessType])) {
            $accessTypes = [];
            foreach (static::getAccessTypesToCheck() as $accType) {
                array_push($accessTypes, $accType);
                if ($minimalAccessType === $accType) {
                    break;
                }
            }
            static::$_accessTypes[$minimalAccessType] = $accessTypes;
        }

        return static::$_accessTypes[$minimalAccessType];
    }

    /**
     * Checks minimal required access type
     * @param string $accessType
     * @param string|null $minimalAccessType
     * @return bool
     */
    public static function checkNeededAccessType(string $accessType, ?string $minimalAccessType = null): bool
    {
        $key = $accessType.$minimalAccessType;
        if (!isset(static::$_accessTypeChecks[$key])) {
            static::$_accessTypeChecks[$key] = in_array($accessType, static::getAccessTypeWithSuperiors($minimalAccessType ?: RolesInterface::ACCESS_TYPE_GUEST));
        }
        return static::$_accessTypeChecks[$key];
    }
}