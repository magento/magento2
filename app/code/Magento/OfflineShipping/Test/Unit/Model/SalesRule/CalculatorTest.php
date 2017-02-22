<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\SalesRule;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Validator|\PHPUnit_Framework_MockObject_MockObject
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
        $this->_model->expects($this->any())->method('_getRules')->will($this->returnValue([]));
    }

    public function testProcessFreeShipping()
    {
        $item = $this->getMock('Magento\Quote\Model\Quote\Item', ['getAddress', '__wakeup'], [], '', false);
        $item->expects($this->once())->method('getAddress')->will($this->returnValue(true));

        $this->assertInstanceOf(
            'Magento\OfflineShipping\Model\SalesRule\Calculator',
            $this->_model->processFreeShipping($item)
        );

        return true;
    }
}
