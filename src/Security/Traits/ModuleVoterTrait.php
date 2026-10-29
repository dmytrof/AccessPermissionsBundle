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
 * Implements ModuleVoterInterface with MODULE constant of AbstractVoter
 */
trait ModuleVoterTrait
{
    public static function getModule(): string
    {
        self::_checkModuleRequirements();

        return static::getConstant('MODULE');
    }

    /**
     * Checks requirements for trait usage
     */
    private static function _checkModuleRequirements(): void
    {
        if (!is_subclass_of(static::class, AbstractVoter::class)) {
            throw new RuntimeException(sprintf('%s does not extend %s', static::class, AbstractVoter::class));
        }
    }
}
