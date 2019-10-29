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

use Symfony\Component\Security\Core\{
    Authentication\Token\TokenInterface, Authorization\AccessDecisionManagerInterface, Authorization\Voter\Voter
};
use Dmytrof\AccessPermissionsBundle\{Exception\BadMethodCallException,
    Exception\RuntimeException,
    Exception\VoterException,
    Security\Traits\AccessTypesTrait};

abstract class AbstractVoter extends Voter implements AccessTypesInterface
{
    use AccessTypesTrait;

    protected const SUBJECT = null;
    public const PREFIX = '';

    public const VIEW   = self::PREFIX.'view';

    public const ATTRIBUTES = [];

    /**
     * @var AccessDecisionManagerInterface
     */
    protected $decisionManager;

    /**
     * AbstractVoter constructor.
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * @return AccessDecisionManagerInterface
     */
    protected function getDecisionManager(): AccessDecisionManagerInterface
    {
        return $this->decisionManager;
    }

    /**
     * Returns constant
     * @param string $constName
     * @return mixed
     */
    protected static function getConstant(string $constName)
    {
        if (!defined('static::'.$constName)) {
            throw new RuntimeException(sprintf('Undefined constant %s at class %s', $constName, static::class));
        }

        return constant('static::'.$constName);
    }

    /**
     * Returns array of attributes
     * @return array
     */
    public static function getAttributes(): array
    {
        return static::ATTRIBUTES;
    }

    /**
     * Checks attribute existence
     * @param string $attribute
     * @return bool
     */
    public static function checkAttribute(string $attribute): bool
    {
        return in_array($attribute, static::getAttributes());
    }

    /**
     * Returns prefix
     * @return string
     */
    public static function getPrefix(): string
    {
        return static::PREFIX;
    }

    /**
     * Returns subject
     * @return array
     */
    public static function getSubject(): array
    {
        if (!in_array(gettype(static::SUBJECT), ['string', 'array'])) {
            throw new VoterException('Constant SUBJECT mut be string or array of strings. Defined: %s', gettype(static::SUBJECT));
        }
        return (array) static::SUBJECT;
    }

    /**
     * Returns attribute short key
     * @param string $attribute
     * @return null|string
     */
    public static function getShortAttribute(string $attribute): ?string
    {
        if (!static::checkAttribute($attribute)) {
            return null;
        }
        return str_replace(static::getPrefix(),'', $attribute);
    }

    /**
     * Returns attribute from short key
     * @param string $shortAttribute
     * @return null|string
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
     * Returns patters for attributes
     * @return string
     */
    protected static function getShortAttributesPattern(): string
    {
        return join('|', array_map('ucfirst', array_map(['static', 'getShortAttribute'], static::getAttributes())));
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject = null)
    {
        // if the attribute isn't one we support, return false
        if (!$this->checkAttribute($attribute)) {
            return false;
        }

        // only vote on objects from static::SUBJECT inside this voter
        if (!is_null($subject)) {
            foreach ($this->getSubject() as $className) {
                if ($subject instanceof $className) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $method = 'can'.ucfirst($this->getAccessType($token)).ucfirst($this->getShortAttribute($attribute));
        return $this->$method($token, $subject);
    }

    /**
     * Returns access for token
     * @param TokenInterface $token
     * @return string
     */
    protected function getAccessType(TokenInterface $token): string
    {
        foreach ($this->getAccessTypes() as $access) {
            if ($this->decisionManager->decide($token, $this->getAccessTypeRoles($access))) {
                return $access;
            }
        }
        return static::ACCESS_TYPE_GUEST;
    }

    /**
     * SuperAdmin can do anything
     * @param string $attribute
     * @param TokenInterface $token
     * @param null $subject
     * @return bool
     */
    protected function canSuperAdmin(string $attribute, TokenInterface $token, $subject = null): bool
    {
        return true;
    }

    /**
     * Decides if accessType can do attribute action for subject
     * @param string $accessType
     * @param string $attribute
     * @param TokenInterface $token
     * @param null $subject
     * @return bool
     */
    protected function can(string $accessType, string $attribute, TokenInterface $token, $subject = null): bool
    {
        return false;
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/^can('.$this->getAccessTypesPattern().')$/s', $name,$matches)) {
            $args = $arguments; // [attribute, token, ?subject]
            array_unshift($args, lcfirst($matches[1])); // access
            return $this->can(...$args); // (access, attribute, token, ?subject)
        }
        if (preg_match('/^can('.$this->getAccessTypesPattern().')('.$this->getShortAttributesPattern().')$/s', $name,$matches)) {
            $args = $arguments; // [token, ?subject]
            array_unshift($args, $this->getAttributeFromShort(lcfirst($matches[2]))); // attribute
            $methodName = 'can'.$matches[1]; // with access
            return $this->$methodName(...$args); // (attribute, token, ?subject)
        }

        throw new BadMethodCallException(sprintf('Undefined method "%s::%s"', get_class($this), $name));
    }
}