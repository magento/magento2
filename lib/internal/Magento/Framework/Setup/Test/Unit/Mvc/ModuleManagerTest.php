<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\EventManager\EventManager;
use Magento\Framework\Setup\Mvc\ModuleManager;
use Magento\Framework\Setup\Mvc\TestModule;
use PHPUnit\Framework\TestCase;

class ModuleManagerTest extends TestCase
{
    public function testLoadModulesMergesConfigAndTriggersEvent(): void
    {
        // Prefer a class that matches the Module naming convention <Namespace>\Module
        require_once dirname(__DIR__) . '/_files/FixtureModule.php';

        $modules = [
            // Base namespace; ModuleManager will append "\\Module"
            __NAMESPACE__ . '\\Fixture',
            __NAMESPACE__ . '\\NoSuchModule',
        ];

        $eventManager = new EventManager();
        $triggered = false;
        $captured = [];
        $eventManager->attach('loadModules.post', function ($e) use (&$triggered, &$captured) {
            $triggered = true;
            $captured = $e->getParams();
        });

        $mm = new ModuleManager($modules, $eventManager);
        $mm->loadModules();

        $this->assertTrue($triggered, 'Expected loadModules.post to be triggered');
        $this->assertArrayHasKey('config', $captured);
        $this->assertSame('bar', $captured['config']['service_manager']['services']['foo'] ?? null);
    }
}
