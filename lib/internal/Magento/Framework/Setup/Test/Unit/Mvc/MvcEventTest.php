<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Test\Unit\Mvc;

use Laminas\ServiceManager\ServiceManager;
use Magento\Framework\Setup\Mvc\MvcApplication;
use Magento\Framework\Setup\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;

class MvcEventTest extends TestCase
{
    public function testSetGetApplication(): void
    {
        $sm = new ServiceManager();
        $app = new MvcApplication($sm);
        $event = new MvcEvent();
        $event->setApplication($app);
        $this->assertSame($app, $event->getApplication());
    }
}
