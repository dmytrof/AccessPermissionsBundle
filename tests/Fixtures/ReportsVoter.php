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

use Dmytrof\AccessPermissionsBundle\Security\ModuleVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Module voter without subject classes
 */
class ReportsVoter extends ModuleVoter
{
    protected const MODULE = 'reports';

    public const PREFIX = 'app.reports.sales.';
    public const VIEW = self::PREFIX.'view';
    public const EXPORT = self::PREFIX.'export';

    public const ATTRIBUTES = [
        self::VIEW,
        self::EXPORT,
    ];

    /**
     * Any other role in the hierarchy can view reports
     */
    protected function can(string $role, string $attribute, TokenInterface $token, mixed $subject = null): bool
    {
        return 'ROLE_USER' === $role && self::VIEW === $attribute;
    }
}
