<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module;

use Magento\Setup\Module\DataSetupFactory;

class DataSetupFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $context = $this->createMock(\Magento\Framework\Module\Setup\Context::class);
        $context->expects($this->once())->method('getEventManager');
        $context->expects($this->once())->method('getLogger');
        $context->expects($this->once())->method('getMigrationFactory');
        $context->expects($this->once())->method('getResourceModel')->willReturn($resource);
        $context->expects($this->once())->method('getFilesystem')->willReturn($filesystem);
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\Module\Setup\Context::class)
            ->willReturn($context);
        $objectManagerProvider = $this->createMock(\Magento\Setup\Model\ObjectManagerProvider::class);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $factory = new DataSetupFactory($objectManagerProvider);
        $this->assertInstanceOf(\Magento\Setup\Module\DataSetup::class, $factory->create());
    }
}
