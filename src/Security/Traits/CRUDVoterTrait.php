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

namespace Dmytrof\AccessPermissionsBundle\Security\Traits;

use Dmytrof\AccessPermissionsBundle\{Exception\RuntimeException, Security\AbstractVoter};

/**
 * Implements CRUDVoterInterface with VIEW, CREATE, EDIT and DELETE constants of AbstractVoter
 */
trait CRUDVoterTrait
{
    public static function getViewAttribute(): string
    {
        self::_checkCRUDRequirements();

        return static::getConstant('VIEW');
    }

    public static function getCreateAttribute(): string
    {
        self::_checkCRUDRequirements();

        return static::getConstant('CREATE');
    }

    public static function getEditAttribute(): string
    {
        self::_checkCRUDRequirements();

        return static::getConstant('EDIT');
    }

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
        if (!is_subclass_of(static::class, AbstractVoter::class)) {
            throw new RuntimeException(sprintf('%s does not extend %s', static::class, AbstractVoter::class));
        }
    }
}
