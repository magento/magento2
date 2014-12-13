<?php
/**
 * @category   Magento
 * @package    Magento_Event
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Event;

/**
 * Class WrapperFactoryTest
 *
 * @package Magento\Framework\Event
 */
class WrapperFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $expectedInstance = 'Magento\Framework\Event\Observer';
        $objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface');

        $wrapperFactory = new WrapperFactory($objectManagerMock);
        $arguments = ['argument' => 'value', 'data' => 'data'];
        $observerInstanceMock = $this->getMock($expectedInstance);

        $objectManagerMock->expects($this->once())
            ->method('create')
            ->with($expectedInstance, $arguments)
            ->will($this->returnValue($observerInstanceMock));

        $this->assertInstanceOf($expectedInstance, $wrapperFactory->create($arguments));
    }
}
