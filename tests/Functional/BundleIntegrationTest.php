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

namespace Dmytrof\AccessPermissionsBundle\Tests\Functional;

use Dmytrof\AccessPermissionsBundle\Entity\AccessAttribute\{AccessAttribute, Repository};
use Dmytrof\AccessPermissionsBundle\Form\Type\{AccessAttributesChoiceType, AccessAttributesCollectionType};
use Dmytrof\AccessPermissionsBundle\Service\{RolesContainer, VotersContainer};
use Dmytrof\AccessPermissionsBundle\Tests\Fixtures\{Admin, Article, ArticleVoter, ReportsVoter, User};
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The bundle in a real kernel with FrameworkBundle, SecurityBundle and DoctrineBundle (SQLite)
 */
class BundleIntegrationTest extends KernelTestCase
{
    private function em(): EntityManagerInterface
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());

        return $em;
    }

    private function isGranted(UserInterface $user, string $attribute, mixed $subject = null): bool
    {
        $container = static::getContainer();
        $container->get('security.token_storage')->setToken(new UsernamePasswordToken($user, 'main', $user->getRoles()));

        return $container->get('security.authorization_checker')->isGranted($attribute, $subject);
    }

    public function testContainersArePublic(): void
    {
        self::bootKernel();

        $this->assertInstanceOf(VotersContainer::class, self::$kernel->getContainer()->get(VotersContainer::class));
        $this->assertInstanceOf(RolesContainer::class, self::$kernel->getContainer()->get(RolesContainer::class));
    }

    public function testRolesFromRoleHierarchy(): void
    {
        self::bootKernel();

        $this->assertEqualsCanonicalizing(
            ['ROLE_AUTHOR', 'ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
            static::getContainer()->get(RolesContainer::class)->getRoles(),
        );
    }

    public function testVotersAreCollected(): void
    {
        self::bootKernel();
        $voters = static::getContainer()->get(VotersContainer::class);

        $this->assertEqualsCanonicalizing([ArticleVoter::class, ReportsVoter::class], array_map(get_class(...), iterator_to_array($voters, false)));
        $this->assertSame(
            array_combine([...ArticleVoter::ATTRIBUTES, ...ReportsVoter::ATTRIBUTES], [...ArticleVoter::ATTRIBUTES, ...ReportsVoter::ATTRIBUTES]),
            $voters->getAllAttributes(),
        );
    }

    public function testAttributeDescriptionsAreTranslated(): void
    {
        self::bootKernel();
        $descriptions = static::getContainer()->get(VotersContainer::class)->getAttributeDescriptionsCollection()->getAsArray();

        $this->assertSame([
            'app.article.create', 'app.article.delete', 'app.article.edit', 'app.article.view',
            'app.reports.sales.export', 'app.reports.sales.view',
        ], array_keys($descriptions));
        $this->assertSame([
            'attribute' => 'app.article.view',
            'shortLabel' => 'View articles',
            'label' => 'Articles > View articles',
            'description' => 'Access to view articles',
            'vendor' => 'Application',
            'module' => null,
            'subject' => 'Articles',
        ], $descriptions['app.article.view']);
        $this->assertSame('Articles > Delete', $descriptions['app.article.delete']['label']);
        $this->assertSame('Reports > Sales > Export', $descriptions['app.reports.sales.export']['label']);

        $module = static::getContainer()->get(VotersContainer::class)->getModuleAttributeDescriptions('reports');
        $this->assertSame(['app.reports.sales.export', 'app.reports.sales.view'], $module->getKeys());
    }

    public function testAuthorizationChecker(): void
    {
        self::bootKernel();
        $author = new User('author', ['ROLE_AUTHOR']);

        $this->assertTrue($this->isGranted($author, ArticleVoter::EDIT, new Article($author)));
        $this->assertFalse($this->isGranted($author, ArticleVoter::EDIT, new Article(new User('another'))));
        // Voters check roles of the token (not roles reachable by the role hierarchy)
        $this->assertFalse($this->isGranted($author, ReportsVoter::VIEW));
        $user = new User('user', ['ROLE_USER']);
        $this->assertTrue($this->isGranted($user, ReportsVoter::VIEW));
        $this->assertFalse($this->isGranted($user, ReportsVoter::EXPORT));

        $admin = new Admin('admin', ['ROLE_ADMIN'], [ArticleVoter::DELETE]);
        $this->assertTrue($this->isGranted($admin, ArticleVoter::DELETE, new Article()));
        $this->assertFalse($this->isGranted($admin, ArticleVoter::EDIT, new Article()));

        $this->assertTrue($this->isGranted(new User('root', ['ROLE_SUPER_ADMIN']), ReportsVoter::EXPORT));
    }

    public function testEntityMappingAndRepository(): void
    {
        self::bootKernel();
        $em = $this->em();
        $em->persist(new AccessAttribute(ArticleVoter::VIEW));
        $em->flush();
        $em->clear();

        $repository = $em->getRepository(AccessAttribute::class);
        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertSame(ArticleVoter::VIEW, $repository->findItemByAttribute(ArticleVoter::VIEW)?->getAttribute());
        $this->assertNull($repository->findItemByAttribute(ArticleVoter::EDIT));
    }

    public function testUniqueAttributeValidation(): void
    {
        self::bootKernel();
        $em = $this->em();
        $em->persist(new AccessAttribute(ArticleVoter::VIEW));
        $em->flush();

        $validator = static::getContainer()->get('validator');
        $violations = $validator->validate(new AccessAttribute(ArticleVoter::VIEW));
        $this->assertCount(1, $violations);
        $this->assertSame('attribute', $violations[0]->getPropertyPath());

        $this->assertCount(0, $validator->validate(new AccessAttribute(ArticleVoter::EDIT)));
        $this->assertCount(1, $validator->validate(new AccessAttribute()));
    }

    public function testChoiceTypeUsesExistingEntities(): void
    {
        self::bootKernel();
        $em = $this->em();
        $em->persist($existing = new AccessAttribute(ArticleVoter::VIEW));
        $em->flush();

        $form = static::getContainer()->get('test.form.factory')->create(AccessAttributesChoiceType::class);
        $this->assertSame(array_combine([...ArticleVoter::ATTRIBUTES, ...ReportsVoter::ATTRIBUTES], [...ArticleVoter::ATTRIBUTES, ...ReportsVoter::ATTRIBUTES]), $form->getConfig()->getOption('choices'));

        $form->submit([ArticleVoter::VIEW, ReportsVoter::EXPORT]);
        $this->assertTrue($form->isSynchronized());

        $data = $form->getData();
        $this->assertInstanceOf(Collection::class, $data);
        $this->assertCount(2, $data);
        $this->assertSame($existing, $data[0]);
        $this->assertNull($data[1]->getId());
        $this->assertSame(ReportsVoter::EXPORT, $data[1]->getAttribute());
    }

    public function testCollectionType(): void
    {
        self::bootKernel();
        $this->em();

        $form = static::getContainer()->get('test.form.factory')->create(AccessAttributesCollectionType::class);
        $form->submit([ArticleVoter::EDIT, 'app.unknown.view']);

        $this->assertSame([ArticleVoter::EDIT], array_map('strval', $form->getData()));
        $this->assertFalse($form->isValid());
    }
}
