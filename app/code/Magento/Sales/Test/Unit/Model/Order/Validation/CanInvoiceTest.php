<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Validation;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Validation\CanInvoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\Order\OrderValidator class
 */
class CanInvoiceTest extends TestCase
{
    /**
     * @var CanInvoice|MockObject
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
     * @var OrderItemInterface|MockObject
     */
    private $orderItemMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStatus', 'getItems'])
            ->getMockForAbstractClass();

        $this->orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQtyToInvoice'])
            ->onlyMethods(['getLockedDoInvoice'])
            ->getMockForAbstractClass();

        $this->model = new CanInvoice();
    }

    /**
     * @param string $state
     *
     * @dataProvider canInvoiceWrongStateDataProvider
     */
    public function testCanInvoiceWrongState($state)
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn($state);
        $this->orderMock->expects($this->never())
            ->method('getItems');
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn('status');
        $this->assertEquals(
            [__('An invoice cannot be created when an order has a status of %1', 'status')],
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * Data provider for testCanInvoiceWrongState
     * @return array
     */
    public static function canInvoiceWrongStateDataProvider()
    {
        return [
            [Order::STATE_PAYMENT_REVIEW],
            [Order::STATE_HOLDED],
            [Order::STATE_CANCELED],
            [Order::STATE_COMPLETE],
            [Order::STATE_CLOSED],
        ];
    }

    public function testCanInvoiceNoItems()
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->assertNotEmpty(
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * @param float $qtyToInvoice
     * @param bool|null $itemLockedDoInvoice
     * @param bool $expectedResult
     *
     * @dataProvider canInvoiceDataProvider
     */
    public function testCanInvoice($qtyToInvoice, $itemLockedDoInvoice, $expectedResult)
    {
        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $items = [$this->orderItemMock];
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->orderItemMock->expects($this->any())
            ->method('getQtyToInvoice')
            ->willReturn($qtyToInvoice);
        $this->orderItemMock->expects($this->any())
            ->method('getLockedDoInvoice')
            ->willReturn($itemLockedDoInvoice);

        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->orderMock)
        );
    }

    /**
     * Data provider for testCanInvoice
     *
     * @return array
     */
    public static function canInvoiceDataProvider()
    {
        return [
            [0, null, [__('The order does not allow an invoice to be created.')]],
            [-1, null, [__('The order does not allow an invoice to be created.')]],
            [1, true, [__('The order does not allow an invoice to be created.')]],
            [0.5, false, []],
        ];
    }
}
