<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\EventManager\EventManager;
use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\MvcServiceManagerConfig;
use PHPUnit\Framework\TestCase;

class MvcServiceManagerConfigTest extends TestCase
{
    public function testConfigureServiceManagerSetsAliasesFactoriesAndInitializer(): void
    {
        $config = new MvcServiceManagerConfig([
            'services' => [ 'x' => 1 ],
        ]);
        $sm = new ServiceManager();

        $config->configureServiceManager($sm);

        // Core services
        $this->assertSame($sm, $sm->get(ServiceManager::class));
        $this->assertInstanceOf(EventManager::class, $sm->get('EventManager'));

        // Aliases
        $this->assertTrue($sm->has('SharedEventManager'));

        // Factories
        $this->assertTrue($sm->has('ServiceListener'));
        $this->assertTrue($sm->has('ModuleManager'));
    }

    public function testToArrayReturnsConfig(): void
    {
        $cfg = new MvcServiceManagerConfig();
        $this->assertIsArray($cfg->toArray());
        $this->assertArrayHasKey('aliases', $cfg->toArray());
    }
}
