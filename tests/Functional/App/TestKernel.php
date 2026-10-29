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

namespace Dmytrof\AccessPermissionsBundle\Tests\Functional\App;

use Dmytrof\AccessPermissionsBundle\DmytrofAccessPermissionsBundle;
use Dmytrof\AccessPermissionsBundle\Tests\Fixtures\{ArticleVoter, ReportsVoter};
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new DoctrineBundle();
        yield new DmytrofAccessPermissionsBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/dmytrof_access_permissions_bundle_tests/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/dmytrof_access_permissions_bundle_tests/log';
    }

    protected function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'form' => ['enabled' => true, 'csrf_protection' => false],
            'validation' => ['enabled' => true],
            'default_locale' => 'en',
            'translator' => ['enabled' => true, 'default_path' => '%kernel.project_dir%/translations', 'fallbacks' => ['en']],
        ]);
        $container->extension('security', [
            'role_hierarchy' => [
                'ROLE_AUTHOR' => ['ROLE_USER'],
                'ROLE_ADMIN' => ['ROLE_USER'],
                'ROLE_SUPER_ADMIN' => ['ROLE_ADMIN'],
            ],
            'providers' => ['users' => ['memory' => null]],
            'firewalls' => ['main' => ['lazy' => true]],
        ]);
        $container->extension('doctrine', [
            'dbal' => ['driver' => 'pdo_sqlite', 'memory' => true],
            'orm' => [
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                // AccessAttribute entity of the bundle is mapped by bundle auto mapping
                'auto_mapping' => true,
            ],
        ]);
        $container->extension('dmytrof_access_permissions', []);

        $services = $container->services()->defaults()->autowire()->autoconfigure();
        // Application voters are collected by autoconfiguration
        $services->set(ArticleVoter::class);
        $services->set(ReportsVoter::class);

        // Keeps test output clean (the default logger writes to stderr)
        $services->set('logger', NullLogger::class);
        // Private services used by the tests
        $services->alias('test.form.factory', 'form.factory')->public();
    }
}
