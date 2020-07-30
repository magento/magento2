<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Setup\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Setup\Module\DataSetup;
use Magento\Setup\Module\DataSetupFactory;
use PHPUnit\Framework\TestCase;

class DataSetupFactoryTest extends TestCase
{
    public function testCreate()
    {
        $resource = $this->createMock(ResourceConnection::class);
        $filesystem = $this->createMock(Filesystem::class);
        $context = $this->createMock(Context::class);
        $context->expects($this->once())->method('getEventManager');
        $context->expects($this->once())->method('getLogger');
        $context->expects($this->once())->method('getMigrationFactory');
        $context->expects($this->once())->method('getResourceModel')->willReturn($resource);
        $context->expects($this->once())->method('getFilesystem')->willReturn($filesystem);
        $objectManager = $this->getMockForAbstractClass(
            ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('get')
            ->with(Context::class)
            ->willReturn($context);
        $objectManagerProvider = $this->createMock(ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $factory = new DataSetupFactory($objectManagerProvider);
        $this->assertInstanceOf(DataSetup::class, $factory->create());
    }
}
