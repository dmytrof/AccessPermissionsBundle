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

namespace Dmytrof\AccessPermissionsBundle\Tests\Security;

use Dmytrof\AccessPermissionsBundle\Exception\{BadMethodCallException, VoterException};
use Dmytrof\AccessPermissionsBundle\Service\RolesContainer;
use Dmytrof\AccessPermissionsBundle\Tests\Fixtures\{Admin, Article, ArticleVoter, ReportsVoter, User};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class VoteTest extends TestCase
{
    private const ROLE_HIERARCHY = [
        'ROLE_AUTHOR' => ['ROLE_USER'],
        'ROLE_ADMIN' => ['ROLE_USER'],
        'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
    ];

    private ArticleVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new ArticleVoter(new RolesContainer(self::ROLE_HIERARCHY));
    }

    private static function token(UserInterface $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, 'main', $user->getRoles());
    }

    public static function votesProvider(): iterable
    {
        $author = new User('author', ['ROLE_AUTHOR']);
        $own = new Article($author);
        $foreign = new Article(new User('another', ['ROLE_AUTHOR']));

        yield 'super admin' => [new User('root', ['ROLE_SUPER_ADMIN']), ArticleVoter::DELETE, $foreign, VoterInterface::ACCESS_GRANTED];
        yield 'admin without admin interface' => [new User('admin', ['ROLE_ADMIN']), ArticleVoter::DELETE, null, VoterInterface::ACCESS_GRANTED];
        yield 'admin with attribute' => [new Admin('admin', ['ROLE_ADMIN'], [ArticleVoter::VIEW]), ArticleVoter::VIEW, $own, VoterInterface::ACCESS_GRANTED];
        yield 'admin without attribute' => [new Admin('admin', ['ROLE_ADMIN'], [ArticleVoter::VIEW]), ArticleVoter::EDIT, $own, VoterInterface::ACCESS_DENIED];
        yield 'author edits own article' => [$author, ArticleVoter::EDIT, $own, VoterInterface::ACCESS_GRANTED];
        yield 'author edits foreign article' => [$author, ArticleVoter::EDIT, $foreign, VoterInterface::ACCESS_DENIED];
        yield 'author views' => [$author, ArticleVoter::VIEW, $foreign, VoterInterface::ACCESS_GRANTED];
        yield 'author creates' => [$author, ArticleVoter::CREATE, null, VoterInterface::ACCESS_GRANTED];
        yield 'author deletes' => [$author, ArticleVoter::DELETE, $own, VoterInterface::ACCESS_DENIED];
        yield 'user' => [new User('user', ['ROLE_USER']), ArticleVoter::VIEW, $own, VoterInterface::ACCESS_DENIED];
        yield 'role unknown to roles container' => [new User('guest', ['ROLE_GUEST']), ArticleVoter::VIEW, $own, VoterInterface::ACCESS_DENIED];
        yield 'unknown role and super admin' => [new User('root', ['ROLE_GUEST', 'ROLE_SUPER_ADMIN']), ArticleVoter::VIEW, $own, VoterInterface::ACCESS_GRANTED];
        yield 'unsupported attribute' => [new User('root', ['ROLE_SUPER_ADMIN']), 'app.comment.view', $own, VoterInterface::ACCESS_ABSTAIN];
        yield 'unsupported subject' => [new User('root', ['ROLE_SUPER_ADMIN']), ArticleVoter::VIEW, new \stdClass(), VoterInterface::ACCESS_ABSTAIN];
    }

    #[DataProvider('votesProvider')]
    public function testVote(UserInterface $user, string $attribute, ?object $subject, int $expected): void
    {
        $this->assertSame($expected, $this->voter->vote(self::token($user), $subject, [$attribute]));
    }

    public function testModuleVoterFallsBackToCan(): void
    {
        $voter = new ReportsVoter(new RolesContainer(self::ROLE_HIERARCHY));
        $user = self::token(new User('user', ['ROLE_USER']));

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($user, null, [ReportsVoter::VIEW]));
        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($user, null, [ReportsVoter::EXPORT]));
        $this->assertSame('reports', ReportsVoter::getModule());
    }

    public function testRolesAddedToRolesContainer(): void
    {
        $rolesContainer = new RolesContainer([]);
        $voter = new ArticleVoter($rolesContainer);
        $token = self::token(new User('author', ['ROLE_AUTHOR']));

        $this->assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, null, [ArticleVoter::VIEW]));

        $rolesContainer->addRole('ROLE_AUTHOR');
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, null, [ArticleVoter::VIEW]));
    }

    public function testSubjectIsNotDefined(): void
    {
        $voter = new ReportsVoter(new RolesContainer(self::ROLE_HIERARCHY));

        $this->expectException(VoterException::class);
        $this->expectExceptionMessage(ReportsVoter::class.'::SUBJECT must be string or array of strings. Defined: null');

        $voter->vote(self::token(new User('user', ['ROLE_USER'])), new Article(), [ReportsVoter::VIEW]);
    }

    public function testUndefinedMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Undefined method "'.ArticleVoter::class.'::canRoleUnknown"');

        $this->voter->canRoleUnknown();
    }
}
