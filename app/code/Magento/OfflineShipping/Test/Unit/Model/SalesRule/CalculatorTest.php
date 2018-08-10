<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\SalesRule;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            ['_getRules', '__wakeup'],
            [],
            '',
            false
        );
    }

    /**
     * @return bool
     */
    public function testProcessFreeShipping()
    {
        $addressMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMock('Magento\Quote\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        $item->expects($this->once())->method('getAddress')->will($this->returnValue($addressMock));

        $this->_model->expects($this->once())
            ->method('_getRules')
            ->with($addressMock)
            ->will($this->returnValue([]));

        $this->assertInstanceOf(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            $this->_model->processFreeShipping($item)
        );

        return true;
    }
}
