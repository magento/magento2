<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\MvcApplication;
use Magento\Framework\Setup\Mvc\TestBootstrapListener;
use Magento\Framework\Setup\Mvc\TestModule;
use PHPUnit\Framework\TestCase;

class MvcApplicationTest extends TestCase
{
    public function testInitBootstrapsServiceManagerAndLoadsModules(): void
    {
        $config = [
            'modules' => [
                __NAMESPACE__ . '\\Fixture\\Module',
            ],
            'service_manager' => [
                'services' => [
                    'foo' => 'bar',
                ],
            ],
            'module_listener_options' => [
                'config_glob_paths' => [],
            ],
            'listeners' => [],
        ];

        require_once dirname(__DIR__) . '/_files/FixtureModule.php';

        $app = MvcApplication::init($config);

        $this->assertInstanceOf(MvcApplication::class, $app);
        $sm = $app->getServiceManager();
        $this->assertInstanceOf(ServiceManager::class, $sm);
        $this->assertSame('bar', $sm->get('foo'));
        $this->assertIsArray($app->getConfig());
    }

    public function testBootstrapInvokesListeners(): void
    {
        $config = [
            'modules' => [],
            'service_manager' => [
                'services' => [],
                'factories' => [],
            ],
            'module_listener_options' => [
                'config_glob_paths' => [],
            ],
            'listeners' => [],
        ];

        $app = MvcApplication::init($config);

        require_once dirname(__DIR__) . '/_files/TestBootstrapListener.php';

        $sm = $app->getServiceManager();
        $sm->setService(TestBootstrapListener::class, new TestBootstrapListener());

        $app->bootstrap([TestBootstrapListener::class]);

        $this->assertTrue(TestBootstrapListener::$bootstrapped);
    }
}
