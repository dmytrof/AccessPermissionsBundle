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

abstract class ModuleVoter extends AbstractVoter implements ModuleVoterInterface
{
    protected const MODULE = null;

    /**
     * Returns module code
     * @return string
     */
    public static function getModule(): string
    {
        return static::MODULE;
    }
}