<?php

/*
 * This file is part of the DmytrofAccessPermissionsBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dmytrof\AccessPermissionsBundle\Security;

use Dmytrof\AccessPermissionsBundle\Security\Traits\ModuleVoterTrait;

abstract class ModuleVoter extends AbstractVoter implements ModuleVoterInterface
{
    use ModuleVoterTrait;

    protected const MODULE = null;
}