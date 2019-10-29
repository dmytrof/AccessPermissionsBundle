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

use Dmytrof\AccessPermissionsBundle\{Exception\RuntimeException, Security\AbstractVoter};

trait CRUDVoterTrait
{
    /**
     * Returns VIEW attribute
     * @return string
     */
    public static function getViewAttribute(): string
    {
        self::_checkCRUDRequirements();
        return static::getConstant('VIEW');
    }

    /**
     * Returns CREATE attribute
     * @return string
     */
    public static function getCreateAttribute(): string
    {
        self::_checkCRUDRequirements();
        return static::getConstant('CREATE');
    }

    /**
     * Returns EDIT attribute
     * @return string
     */
    public static function getEditAttribute(): string
    {
        self::_checkCRUDRequirements();
        return static::getConstant('EDIT');
    }

    /**
     * Returns DELETE attribute
     * @return string
     */
    public static function getDeleteAttribute(): string
    {
        self::_checkCRUDRequirements();
        return static::getConstant('DELETE');
    }

    /**
     * Checks requirements for trait usage
     */
    private static function _checkCRUDRequirements(): void
    {
        if (!is_subclass_of(static::class, AbstractVoter::class) ) {
            throw new RuntimeException(sprintf('%s is not extends %s interface', static::class, AbstractVoter::class));
        }
    }
}