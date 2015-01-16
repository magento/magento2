<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Items\Column;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class DefaultColumnTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
     */
    protected $defaultColumn;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->defaultColumn = $this->objectManagerHelper->getObject(
            'Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn'
        );
        $this->itemMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getRowTotal', 'getDiscountAmount', 'getBaseRowTotal', 'getBaseDiscountAmount', '__wakeup'])
            ->getMock();
    }

    public function testGetTotalAmount()
    {
        $rowTotal = 10;
        $discountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getRowTotal')
            ->will($this->returnValue($rowTotal));
        $this->itemMock->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue($discountAmount));
        $this->assertEquals($expectedResult, $this->defaultColumn->getTotalAmount($this->itemMock));
    }

    public function testGetBaseTotalAmount()
    {
        $baseRowTotal = 10;
        $baseDiscountAmount = 2;
        $expectedResult = 8;
        $this->itemMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->will($this->returnValue($baseRowTotal));
        $this->itemMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->will($this->returnValue($baseDiscountAmount));
        $this->assertEquals($expectedResult, $this->defaultColumn->getBaseTotalAmount($this->itemMock));
    }
}
