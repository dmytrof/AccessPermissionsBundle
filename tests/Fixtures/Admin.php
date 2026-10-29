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

namespace Dmytrof\AccessPermissionsBundle\Tests\Fixtures;

use Dmytrof\AccessPermissionsBundle\Security\AdminInterface;

class Admin extends User implements AdminInterface
{
    /**
     * @param list<string> $roles
     * @param list<string> $accessAttributes
     */
    public function __construct(string $identifier, array $roles = [], private readonly array $accessAttributes = [])
    {
        parent::__construct($identifier, $roles);
    }

    public function getAdminAccessAttributes(): array
    {
        return $this->accessAttributes;
    }
}
