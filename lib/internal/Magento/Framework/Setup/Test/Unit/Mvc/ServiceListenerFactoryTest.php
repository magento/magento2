<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\ServiceListenerFactory;
use PHPUnit\Framework\TestCase;

class ServiceListenerFactoryTest extends TestCase
{
    public function testFactoryReturnsServiceListener(): void
    {
        $sm = new ServiceManager();
        $factory = new ServiceListenerFactory();
        $listener = $factory($sm, 'ServiceListener');
        $this->assertInstanceOf(ServiceListener::class, $listener);
    }
}
