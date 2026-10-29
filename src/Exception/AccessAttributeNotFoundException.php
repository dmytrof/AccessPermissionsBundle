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

namespace Dmytrof\AccessPermissionsBundle\Exception;

use Dmytrof\AccessPermissionsBundle\Exception;

class AccessAttributeNotFoundException extends AccessAttributeException implements Exception
{
    public function __construct(string $message = 'Access attribute not found', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
