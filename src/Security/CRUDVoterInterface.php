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

interface CRUDVoterInterface extends AccessTypesInterface
{
    /**
     * Returns VIEW attribute
     * @return string
     */
    public static function getViewAttribute(): string;

    /**
     * Returns CREATE attribute
     * @return string
     */
    public static function getCreateAttribute(): string;

    /**
     * Returns EDIT attribute
     * @return string
     */
    public static function getEditAttribute(): string;

    /**
     * Returns DELETE attribute
     * @return string
     */
    public static function getDeleteAttribute(): string;
}