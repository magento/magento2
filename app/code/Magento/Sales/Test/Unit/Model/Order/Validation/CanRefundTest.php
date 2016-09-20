<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Validation;

use Magento\Sales\Model\Order;

/**
 * Class CanRefundTest
 */
class CanRefundTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Validation\CanRefund|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getItems'])
            ->getMockForAbstractClass();

        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);
        $this->model = new \Magento\Sales\Model\Order\Validation\CanRefund(
            $this->priceCurrencyMock
        );
    }

    /**
     * @param string $state
     *
     * @dataProvider canCreditmemoWrongStateDataProvider
     */
    public function testCanCreditmemoWrongState($state)
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn($state);
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('status');
        $this->orderMock->expects($this->never())
            ->method('getTotalPaid')
            ->willReturn(15);
        $this->orderMock->expects($this->never())
            ->method('getTotalRefunded')
            ->willReturn(14);
        $this->assertEquals(
            [__('A creditmemo can not be created when an order has a status of %1', 'status')],
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * Data provider for testCanCreditmemoWrongState
     * @return array
     */
    public function canCreditmemoWrongStateDataProvider()
    {
        return [
            [Order::STATE_PAYMENT_REVIEW],
            [Order::STATE_HOLDED],
            [Order::STATE_CANCELED],
            [Order::STATE_CLOSED],
        ];
    }

    public function testCanCreditmemoNoMoney()
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);
        $this->orderMock->expects($this->once())
            ->method('getTotalPaid')
            ->willReturn(15);
        $this->orderMock->expects($this->once())
            ->method('getTotalRefunded')
            ->willReturn(15);
        $this->assertEquals(
            [
                __('The order does not allow a creditmemo to be created.')
            ],
            $this->model->validate($this->orderMock)
        );
    }
}
