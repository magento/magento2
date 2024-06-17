<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Validation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Validation\CanRefund;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CanRefundTest extends TestCase
{
    /**
     * @var CanRefund|MockObject
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $objects = [
            [
                ScopeConfigInterface::class,
                $this->createMock(ScopeConfigInterface::class)
            ]
        ];
        $this->objectManager->prepareObjectManager($objects);
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStatus', 'getItems'])
            ->getMockForAbstractClass();

        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnArgument(0);
        $this->model = new CanRefund(
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
    public static function canCreditmemoWrongStateDataProvider()
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
        $this->orderMock->expects($this->any())
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
