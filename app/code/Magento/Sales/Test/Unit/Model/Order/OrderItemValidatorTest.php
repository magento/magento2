<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order;

/**
 * Test for \Magento\Sales\Model\Order\OrderItemValidator class
 */
class OrderItemValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\OrderItemValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderItemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getQtyToInvoice'])
            ->getMock();

        $this->model = new \Magento\Sales\Model\Order\OrderItemValidator();
    }

    /**
     * @param float $qty
     * @param bool $expectedResult
     *
     * @dataProvider canInvoiceDataProvider
     */
    public function testCanInvoice($qty, $expectedResult)
    {
        $this->orderItemMock->expects($this->once())
            ->method('getQtyToInvoice')
            ->willReturn($qty);
        $this->assertEquals($expectedResult, $this->model->canInvoice($this->orderItemMock));
    }

    public function canInvoiceDataProvider()
    {
        return [
            [1, true],
            [0, false],
            [-1, false]
        ];
    }
}
