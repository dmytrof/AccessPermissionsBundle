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

namespace Dmytrof\AccessPermissionsBundle\Security;

use Dmytrof\AccessPermissionsBundle\{Exception\BadMethodCallException, Exception\RuntimeException, Exception\VoterException, Service\RolesContainer};
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\{Vote, Voter};

/**
 * Votes on ATTRIBUTES for subjects of SUBJECT classes (or without subject).
 *
 * For every role of the token the voter calls the first existing method:
 *   can{Role}{ShortAttribute}(TokenInterface $token, $subject = null), e.g. canRoleAuthorEdit
 *   can{Role}(string $attribute, TokenInterface $token, $subject = null), e.g. canRoleAuthor
 *   can(string $role, string $attribute, TokenInterface $token, $subject = null)
 * Access is granted when any of the calls returns true.
 */
abstract class AbstractVoter extends Voter
{
    /**
     * Class name or list of class names of supported subjects
     *
     * @var class-string|list<class-string>|null
     */
    protected const SUBJECT = null;
    public const PREFIX = '';

    public const VIEW = self::PREFIX.'view';

    /**
     * @var list<string>
     */
    public const ATTRIBUTES = [];

    public function __construct(
        protected RolesContainer $rolesContainer,
    ) {
    }

    public function getRolesContainer(): RolesContainer
    {
        return $this->rolesContainer;
    }

    /**
     * Returns constant of the voter
     *
     * @throws RuntimeException when the constant is not defined
     */
    protected static function getConstant(string $constName): mixed
    {
        if (!defined('static::'.$constName)) {
            throw new RuntimeException(sprintf('Undefined constant %s at class %s', $constName, static::class));
        }

        return constant('static::'.$constName);
    }

    /**
     * Returns array of attributes
     *
     * @return list<string>
     */
    public static function getAttributes(): array
    {
        return static::ATTRIBUTES;
    }

    /**
     * Checks attribute existence
     */
    public static function checkAttribute(string $attribute): bool
    {
        return in_array($attribute, static::getAttributes(), true);
    }

    public static function getPrefix(): string
    {
        return static::PREFIX;
    }

    /**
     * Returns supported subject classes
     *
     * @return list<class-string>
     *
     * @throws VoterException when SUBJECT is not defined
     */
    public static function getSubject(): array
    {
        if (!is_string(static::SUBJECT) && !is_array(static::SUBJECT)) {
            throw new VoterException(sprintf('Constant %s::SUBJECT must be string or array of strings. Defined: %s', static::class, get_debug_type(static::SUBJECT)));
        }

        return (array) static::SUBJECT;
    }

    /**
     * Returns attribute short key (attribute without prefix)
     */
    public static function getShortAttribute(string $attribute): ?string
    {
        if (!static::checkAttribute($attribute)) {
            return null;
        }

        return str_replace(static::getPrefix(), '', $attribute);
    }

    /**
     * Returns attribute from short key
     */
    protected static function getAttributeFromShort(string $shortAttribute): ?string
    {
        $attribute = static::getPrefix().$shortAttribute;
        if (!static::checkAttribute($attribute)) {
            return null;
        }

        return $attribute;
    }

    /**
     * Returns regex pattern of short attributes
     */
    protected static function getShortAttributesPattern(): string
    {
        return implode('|', array_map(
            static fn (string $attribute): string => preg_quote(ucfirst((string) static::getShortAttribute($attribute)), '/'),
            static::getAttributes(),
        ));
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!static::checkAttribute($attribute)) {
            return false;
        }

        // only vote on objects from static::SUBJECT inside this voter
        if (null !== $subject) {
            foreach (static::getSubject() as $className) {
                if ($subject instanceof $className) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Roles of the token unknown to RolesContainer are skipped
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $rolesContainer = $this->getRolesContainer();
        foreach ($token->getRoleNames() as $role) {
            if (!$rolesContainer->hasRole($role)) {
                continue;
            }
            $method = 'can'.$rolesContainer->classifyRole($role).ucfirst((string) static::getShortAttribute($attribute));
            if ($this->$method($token, $subject)) {
                return true;
            }
        }

        return false;
    }

    /**
     * ROLE_SUPER_ADMIN can do anything
     */
    protected function canRoleSuperAdmin(string $attribute, TokenInterface $token, mixed $subject = null): bool
    {
        return true;
    }

    /**
     * Checks if admin has access to attribute (users not implementing AdminInterface have access)
     */
    protected function canRoleAdmin(string $attribute, TokenInterface $token, mixed $subject = null): bool
    {
        $admin = $token->getUser();
        if (!$admin instanceof AdminInterface) {
            return true;
        }

        return in_array($attribute, $admin->getAdminAccessAttributes());
    }

    /**
     * Decides if role can do attribute action for subject
     */
    protected function can(string $role, string $attribute, TokenInterface $token, mixed $subject = null): bool
    {
        return false;
    }

    /**
     * Resolves can{Role}{ShortAttribute}() to can{Role}() and can{Role}() to can()
     *
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments): mixed
    {
        $rolesPattern = $this->getRolesContainer()->getRolesPattern();
        if ('' !== $rolesPattern) {
            if (preg_match('/^can('.$rolesPattern.')$/s', $name, $matches)) {
                // (role, attribute, token, ?subject)
                return $this->can($this->getRolesContainer()->declassifyRole($matches[1]), ...$arguments);
            }
            $attributesPattern = static::getShortAttributesPattern();
            if ('' !== $attributesPattern && preg_match('/^can('.$rolesPattern.')('.$attributesPattern.')$/s', $name, $matches)) {
                $methodName = 'can'.$matches[1];

                // (attribute, token, ?subject)
                return $this->$methodName(static::getAttributeFromShort(lcfirst($matches[2])), ...$arguments);
            }
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s::%s"', static::class, $name));
    }
}
