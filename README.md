DmytrofAccessPermissionsBundle
====================

This bundle helps you to create access permissions for 
your Symfony 4 application

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
                // Return savet attributes from DB
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
        
        /**
         * @Route("/api/articles")
         */
        class ArticleController extends AbstractController
        {
            /**
             * @Route("/", methods={"GET"})
             */
            public function getAll(Request $request)
            {
                $this->denyAccessUnlessGranted(ArticleVoter::getViewAttribute());
        
                // Fetch article from DB and return
            }
        }
        
    
At this moment AccessDecisionManager "asks" ArticleVoter if canRoleAdmin to view:
   
        // Dmytrof\AccessPermissionsBundle\Security\AbstractVoter.php;
        
        /**
         * Checks if admin has access to attribute
         */
        protected function canRoleAdmin(string $attribute, TokenInterface $token, $subject = null): bool
        {
            $admin = $token->getUser();
            if (!$admin instanceof AdminInterface) {
                return true;
            }
            return in_array($attribute, $admin->getAdminAccessAttributes());
        }
        

You can write "can-method" for any defined role, which defined at security.yaml:
        
        security:
            role_hierarchy:
                ROLE_AUTHOR
                ROLE_ADMIN: ROLE_USER
                ROLE_SUPER_ADMIN: ROLE_ADMIN 
                
or added to RolesContainer:

        $rolesContainer->addRole('ROLE_ANY');
        
Add **canRoleAuthorEdit** (where RoleAuthor - classified role ROLE_AUTHOR, Edit - short 
attribute ArticleVoter::EDIT) 

        // src/Security/ArticleVoter.php
        
        /**
         * Checks if ROLE_AUTHOR can EDIT the article
         */
        protected function canRoleAuthorEdit(TokenInterface $token, $subject = null): bool
        {
            return $subject instanceof Article  // Subject is Article
                && $token->getUser() instanceof Author  // Authinticated user is Author
                && $subject->getAuthor() === $token->getUser(); // Authenticated Author is author of the Article
        }
        
        /**
         * Checks if ROLE_AUTHOR can VIEW, CREATE, DELETE the article
         */
        protected function canRoleAuthor(string $attribute, TokenInterface $token, $subject = null): bool
        {
            switch($attribute) {
                case static::VIEW:
                case static::CREATE:
                    return true;
                default:
                    return false;
            }
        }
        
**Important to remember:** 
1. Voter tries to call **canRoleAuthorEdit** first. 
2. If method not exists **canRoleAuthor**  will be called
3. If method not exists **can** will be called