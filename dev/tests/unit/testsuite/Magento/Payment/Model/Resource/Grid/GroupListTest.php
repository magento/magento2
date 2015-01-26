<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Resource\Grid;

class GroupListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Resource\Grid\GroupsList
     */
    protected $groupArrayModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->helperMock = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $this->groupArrayModel = new \Magento\Payment\Model\Resource\Grid\GroupList($this->helperMock);
    }

    public function testToOptionArray()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('getPaymentMethodList')
            ->with(true, true, true)
            ->will($this->returnValue(['group data']));
        $this->assertEquals(['group data'], $this->groupArrayModel->toOptionArray());
    }
}
