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

use Dmytrof\AccessPermissionsBundle\Security\{AbstractVoter, CRUDVoterInterface, Traits\CRUDVoterTrait};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ArticleVoter extends AbstractVoter implements CRUDVoterInterface
{
    use CRUDVoterTrait;

    protected const SUBJECT = [
        Article::class,
    ];

    public const PREFIX = 'app.article.';

    public const VIEW = self::PREFIX.'view';
    public const CREATE = self::PREFIX.'create';
    public const EDIT = self::PREFIX.'edit';
    public const DELETE = self::PREFIX.'delete';

    public const ATTRIBUTES = [
        self::VIEW,
        self::CREATE,
        self::EDIT,
        self::DELETE,
    ];

    /**
     * ROLE_AUTHOR can EDIT own articles
     */
    protected function canRoleAuthorEdit(TokenInterface $token, mixed $subject = null): bool
    {
        return $subject instanceof Article && null !== $subject->author && $subject->author === $token->getUser();
    }

    /**
     * ROLE_AUTHOR can VIEW and CREATE articles
     */
    protected function canRoleAuthor(string $attribute, TokenInterface $token, mixed $subject = null): bool
    {
        return in_array($attribute, [self::VIEW, self::CREATE], true);
    }
}
