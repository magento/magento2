<?php
/**
 * @category   Magento
 * @package    Magento_Event
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Test\Unit;

use \Magento\Framework\Event\WrapperFactory;

/**
 * Class WrapperFactoryTest
 *
 * @package Magento\Framework\Event
 */
class WrapperFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $expectedInstance = \Magento\Framework\Event\Observer::class;
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $wrapperFactory = new WrapperFactory($objectManagerMock);
        $arguments = ['argument' => 'value', 'data' => 'data'];
        $observerInstanceMock = $this->createMock($expectedInstance);

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstance, $arguments)
            ->willReturn($observerInstanceMock);

        $this->assertInstanceOf($expectedInstance, $wrapperFactory->create($arguments));
    }
}
