<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\SalesRule;

class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->createPartialMock(
            \Magento\OfflineShipping\Model\SalesRule\Calculator::class,
            ['_getRules', '__wakeup']
        );
    }

    /**
     * @return bool
     */
    public function testProcessFreeShipping()
    {
        $addressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->createPartialMock(\Magento\Quote\Model\Quote\Item::class, ['getAddress', '__wakeup']);
        $item->expects($this->once())->method('getAddress')->will($this->returnValue($addressMock));

        $this->_model->expects($this->once())
            ->method('_getRules')
            ->with($addressMock)
            ->will($this->returnValue([]));

        $this->assertInstanceOf(
            \Magento\OfflineShipping\Model\SalesRule\Calculator::class,
            $this->_model->processFreeShipping($item)
        );

        return true;
    }
}
