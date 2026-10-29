DmytrofAccessPermissionsBundle
====================

This bundle helps you to create access permissions for
your Symfony application

## Requirements

* PHP 8.4 or higher
* Symfony 6.4, 7.x or 8.x (FrameworkBundle, SecurityBundle, Form, Validator, Translation)
* Doctrine ORM 3 (with DoctrineBundle)

## Installation

### Step 1: Install the bundle

    $ composer require dmytrof/access-permissions-bundle 
    
### Step 2: Enable the bundle

    <?php
        // config/bundles.php
        
        return [
            // ...
            Dmytrof\AccessPermissionsBundle\DmytrofAccessPermissionsBundle::class => ['all' => true],
        ];

### Step 3: Configure (optional)

    # config/packages/dmytrof_access_permissions.yaml
    dmytrof_access_permissions:
        translation_domain: access_attributes # default

`Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\AccessAttribute` entity (table `dmytrof_access_attribute`)
is mapped with PHP attributes. It is mapped automatically with `doctrine.orm.auto_mapping: true`, otherwise add the mapping:

    # config/packages/doctrine.yaml
    doctrine:
        orm:
            mappings:
                DmytrofAccessPermissionsBundle:
                    type: attribute
                    is_bundle: true

## Usage

Read [official documentation for symfony/security](https://symfony.com/doc/current/security.html) 
and install security component to your project.

#### 1. Create voter for Article entity:
        
        // src/Security/ArticleVoter.php
        
        use App\Model\{Article, Author};
        use Dmytrof\AccessPermissionsBundle\Security\{AbstractVoter, CRUDVoterInterface, Traits\CRUDVoterTrait};
        use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
        
        class ArticleVoter extends AbstractVoter implements CRUDVoterInterface
        {
            use CRUDVoterTrait;
        
            // Put needed resources to subject (Article, Category etc.)
            protected const SUBJECT = [
                Article::class,
            ];
        
            public const PREFIX = 'app.article.';
        
            public const VIEW   = self::PREFIX.'view';
            public const CREATE = self::PREFIX.'create';
            public const EDIT   = self::PREFIX.'edit';
            public const DELETE = self::PREFIX.'delete';
        
            public const ATTRIBUTES = [
                self::VIEW,
                self::CREATE,
                self::EDIT,
                self::DELETE,
            ];
        }
    

#### 2. Add AdminInterface to your User model:
    
        // src/Entity/User.php
    
        use Symfony\Component\Security\Core\User\UserInterface;
        use Dmytrof\AccessPermissionsBundle\Security\AdminInterface;
        
        class User implements UserInterface, AdminInterface
        {
            //.....
            
            /**
             * Returns admin access atributes
             */
            public function getAdminAccessAttributes(): array
            {
                // Return saved attributes from DB
                return [
                    ArticleVoter::getViewAttribute(),
                    ArticleVoter::getCreateAttribute(),
                    ArticleVoter::getEditAttribute(),
                ];
            }
            
            /**
             * Returns roles
             */
            public function getRoles(): array
            {
                $roles = $this->roles;
                // guarantee every user at least has ROLE_USER
                $roles[] = 'ROLE_USER';
                if ($this->isAdmin()) {
                    $roles[] = 'ROLE_ADMIN';
                }
            
                return array_unique($roles);
            }
            //....
        }
        
#### 3. Add access check to controller:

        //src/Controller/ArticleController.php

        #[Route('/api/articles')]
        class ArticleController extends AbstractController
        {
            #[Route('/', methods: ['GET'])]
            public function getAll(Request $request): Response
            {
                $this->denyAccessUnlessGranted(ArticleVoter::getViewAttribute());
        
                // Fetch article from DB and return response
            }
        }
        
    
At this moment AccessDecisionManager "asks" ArticleVoter if canRoleAdmin to view:
   
        // Dmytrof\AccessPermissionsBundle\Security\AbstractVoter.php;
        
        /**
         * Checks if admin has access to attribute
         */
        protected function canRoleAdmin(string $attribute, TokenInterface $token, mixed $subject = null): bool
        {
            $admin = $token->getUser();
            if (!$admin instanceof AdminInterface) {
                return true;
            }
            return in_array($attribute, $admin->getAdminAccessAttributes());
        }
        

You can write "can-method" for any role which is defined at security.yaml:
        
        security:
            role_hierarchy:
                ROLE_AUTHOR
                ROLE_ADMIN: ROLE_USER
                ROLE_SUPER_ADMIN: ROLE_ADMIN 
                
or added to RolesContainer:

        /** @var \Dmytrof\AccessPermissionsBundle\Service\RolesContainer $rolesContainer */
        $rolesContainer->addRole('ROLE_ANY');
        
Add **canRoleAuthorEdit** (where RoleAuthor - classified role ROLE_AUTHOR, Edit - short 
attribute ArticleVoter::EDIT) 

        // src/Security/ArticleVoter.php
        
        /**
         * Checks if ROLE_AUTHOR can EDIT the article
         */
        protected function canRoleAuthorEdit(TokenInterface $token, mixed $subject = null): bool
        {
            return $subject instanceof Article  // Subject is Article
                && $token->getUser() instanceof Author  // Authinticated user is Author
                && $subject->getAuthor() === $token->getUser(); // Authenticated Author is author of the Article
        }
        
        /**
         * Checks if ROLE_AUTHOR can VIEW, CREATE, DELETE the article
         */
        protected function canRoleAuthor(string $attribute, TokenInterface $token, mixed $subject = null): bool
        {
            return in_array($attribute, [static::VIEW, static::CREATE], true);
        }
        
**Important to remember:**
1. Voter tries to call **canRoleAuthorEdit** first.
2. If method not exists **canRoleAuthor**  will be called
3. If method not exists **can** will be called
4. Only roles of the token are checked (roles reachable by the role hierarchy are not added),
   roles unknown to RolesContainer are skipped

#### 4. Add attributes labels and descriptions:

Create **access_attributes.en.yaml** at translations folder
        
        // translations/access_attributes.en.yaml
        
        app:
            label: My application
            attributes:
                create:
                    label: Create
                view:
                    label: View
                edit:
                    label: Edit
                delete:
                    label: Delete
            subjects:
                article:
                    label: Articles
                    attributes:
                        view:
                            label: View articles
                            description: Access to view article(s)
                        create:
                            label: Create new articles
                        # edit - default label app.attributes.edit.label will be used
                        # delete - default label app.attributes.delete.label will be used
                author:
                    label: Authors
                    # attributes - default app.attributes will be used
                

#### 5. Manage user attributes:
        
Use form type **AccessAttributesChoiceType** or **AccessAttributesCollectionType** at your user form type 
to manage access attributes for user.

To get all access attributes with descriptions at your API add action to your UserController:

        // src/Controller/UserController.php
        
        public function getAccessAttributes(VotersContainer $votersContainer): JsonResponse
        {
            return new JsonResponse([
                'attributes' => $votersContainer->getAttributeDescriptionsCollection()->sort()->getAsArray(),
            ]);
        }

## Upgrading from 1.x (PHP 8.4, Symfony 6.4+, Doctrine ORM 3)

* Doctrine mapping and validation constraints use PHP attributes; `AccessAttribute` model and entity properties are typed
  (`protected ?int $id`, `protected ?string $attribute`): redeclare them with the same types in your subclasses.
* Voter methods are typed: `supports(string $attribute, mixed $subject)`,
  `voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null)`,
  can-methods accept `mixed $subject = null`.
* Roles of the token unknown to RolesContainer are skipped instead of throwing `BadMethodCallException`.
* `RolesContainer::addRole()` throws `RoleException` for roles not beginning with `ROLE_`;
  `addRole()` and `removeRole()` are variadic.
* `AccessAttributeType` uses a copy of the transformer for every form (the shared transformer is not changed).
* Services are defined in `Resources/config/services.php` (only bundle services are registered); `symfony/yaml` is not needed.

## Tests

    $ composer install
    $ vendor/bin/phpunit