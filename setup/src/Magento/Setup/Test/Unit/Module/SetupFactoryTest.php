<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module;

use Magento\Setup\Module\SetupFactory;

class SetupFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\App\ResourceConnection::class)
            ->willReturn($this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false));
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $factory = new SetupFactory($objectManagerProvider);
        $this->assertInstanceOf(\Magento\Setup\Module\Setup::class, $factory->create());
    }

    public function testCreateWithParam()
    {
        $objectManager = $this->getMockForAbstractClass(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            '',
            false
        );
        $objectManager->expects($this->never())->method('get');
        $resource = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $objectManagerProvider = $this->getMock(\Magento\Setup\Model\ObjectManagerProvider::class, [], [], '', false);
        $objectManagerProvider->expects($this->once())->method('get')->willReturn($objectManager);
        $factory = new SetupFactory($objectManagerProvider);
        $this->assertInstanceOf(\Magento\Setup\Module\Setup::class, $factory->create($resource));
    }
}
