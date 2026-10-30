<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\EventManager\EventManager;
use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\ModuleManager;
use Magento\Framework\Setup\Mvc\ModuleManagerFactory;
use PHPUnit\Framework\TestCase;

class ModuleManagerFactoryTest extends TestCase
{
    public function testFactoryCreatesModuleManagerWithConfigModulesAndEventManager(): void
    {
        $sm = new ServiceManager();
        $sm->setService('ApplicationConfig', [
            'modules' => ['Foo\\Bar'],
        ]);
        $sm->setService('EventManager', new EventManager());

        $factory = new ModuleManagerFactory();
        $instance = $factory($sm, 'ModuleManager');

        $this->assertInstanceOf(ModuleManager::class, $instance);
    }
}
