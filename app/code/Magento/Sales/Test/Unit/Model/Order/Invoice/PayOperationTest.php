<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Invoice;

/**
 * Unit test for Invoice pay operation.
 */
class PayOperationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\PayOperation
     */
    private $subject;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceMock;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $invoiceItemMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderPaymentMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $paymentMethodMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->orderMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getPayment',
                'setTotalInvoiced',
                'getTotalInvoiced',
                'setBaseTotalInvoiced',
                'getBaseTotalInvoiced',
                'setSubtotalInvoiced',
                'getSubtotalInvoiced',
                'setBaseSubtotalInvoiced',
                'getBaseSubtotalInvoiced',
                'setTaxInvoiced',
                'getTaxInvoiced',
                'setBaseTaxInvoiced',
                'getBaseTaxInvoiced',
                'setDiscountTaxCompensationInvoiced',
                'getDiscountTaxCompensationInvoiced',
                'setBaseDiscountTaxCompensationInvoiced',
                'getBaseDiscountTaxCompensationInvoiced',
                'setShippingTaxInvoiced',
                'getShippingTaxInvoiced',
                'setBaseShippingTaxInvoiced',
                'getBaseShippingTaxInvoiced',
                'setShippingInvoiced',
                'getShippingInvoiced',
                'setBaseShippingInvoiced',
                'getBaseShippingInvoiced',
                'setDiscountInvoiced',
                'getDiscountInvoiced',
                'setBaseDiscountInvoiced',
                'getBaseDiscountInvoiced',
                'setBaseTotalInvoicedCost',
                'getBaseTotalInvoicedCost',
            ]
        );
        $this->orderMock->expects($this->any())
            ->method('getTotalInvoiced')
            ->willReturn(43);
        $this->orderMock->expects($this->any())
            ->method('getBaseTotalInvoiced')
            ->willReturn(43);
        $this->orderMock->expects($this->any())
            ->method('getSubtotalInvoiced')
            ->willReturn(22);
        $this->orderMock->expects($this->any())
            ->method('getBaseSubtotalInvoiced')
            ->willReturn(22);
        $this->orderMock->expects($this->any())
            ->method('getTaxInvoiced')
            ->willReturn(15);
        $this->orderMock->expects($this->any())
            ->method('getBaseTaxInvoiced')
            ->willReturn(15);
        $this->orderMock->expects($this->any())
            ->method('getDiscountTaxCompensationInvoiced')
            ->willReturn(11);
        $this->orderMock->expects($this->any())
            ->method('getBaseDiscountTaxCompensationInvoiced')
            ->willReturn(11);
        $this->orderMock->expects($this->any())
            ->method('getShippingTaxInvoiced')
            ->willReturn(12);
        $this->orderMock->expects($this->any())
            ->method('getBaseShippingTaxInvoiced')
            ->willReturn(12);
        $this->orderMock->expects($this->any())
            ->method('getShippingInvoiced')
            ->willReturn(28);
        $this->orderMock->expects($this->any())
            ->method('getBaseShippingInvoiced')
            ->willReturn(28);
        $this->orderMock->expects($this->any())
            ->method('getDiscountInvoiced')
            ->willReturn(19);
        $this->orderMock->expects($this->any())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(19);
        $this->orderMock->expects($this->any())
            ->method('getBaseTotalInvoicedCost')
            ->willReturn(31);

        $this->invoiceMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\InvoiceInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getItems',
                'getState',
                'capture',
                'setCanVoidFlag',
                'pay',
                'getGrandTotal',
                'getBaseGrandTotal',
                'getSubtotal',
                'getBaseSubtotal',
                'getTaxAmount',
                'getBaseTaxAmount',
                'getDiscountTaxCompensationAmount',
                'getBaseDiscountTaxCompensationAmount',
                'getShippingTaxAmount',
                'getBaseShippingTaxAmount',
                'getShippingAmount',
                'getBaseShippingAmount',
                'getDiscountAmount',
                'getBaseDiscountAmount',
                'getBaseCost',
            ]
        );
        $this->invoiceMock->expects($this->any())
            ->method('getGrandTotal')
            ->willReturn(43);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseGrandTotal')
            ->willReturn(43);
        $this->invoiceMock->expects($this->any())
            ->method('getSubtotal')
            ->willReturn(22);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseSubtotal')
            ->willReturn(22);
        $this->invoiceMock->expects($this->any())
            ->method('getTaxAmount')
            ->willReturn(15);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseTaxAmount')
            ->willReturn(15);
        $this->invoiceMock->expects($this->any())
            ->method('getDiscountTaxCompensationAmount')
            ->willReturn(11);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn(11);
        $this->invoiceMock->expects($this->any())
            ->method('getShippingTaxAmount')
            ->willReturn(12);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseShippingTaxAmount')
            ->willReturn(12);
        $this->invoiceMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(28);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseShippingAmount')
            ->willReturn(28);
        $this->invoiceMock->expects($this->any())
            ->method('getDiscountAmount')
            ->willReturn(19);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseDiscountAmount')
            ->willReturn(19);
        $this->invoiceMock->expects($this->any())
            ->method('getBaseCost')
            ->willReturn(31);

        $this->contextMock = $this->createMock(\Magento\Framework\Model\Context::class);

        $this->invoiceItemMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\InvoiceItemInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'isDeleted',
                'register',
            ]
        );
        $this->invoiceItemMock->expects($this->any())
            ->method('isDeleted')
            ->willReturn(false);
        $this->invoiceItemMock->expects($this->any())
            ->method('getQty')
            ->willReturn(1);

        $this->orderPaymentMock = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderPaymentInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'canCapture',
                'getMethodInstance',
                'getIsTransactionPending',
            ]
        );
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->orderPaymentMock);

        $this->eventManagerMock = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $this->paymentMethodMock = $this->getMockForAbstractClass(
            \Magento\Payment\Model\MethodInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );
        $this->orderPaymentMock->expects($this->any())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->subject = new \Magento\Sales\Model\Order\Invoice\PayOperation(
            $this->contextMock
        );
    }

    /**
     * @param bool|null $canCapture
     * @param bool|null $isOnline
     * @param bool|null $isGateway
     * @param bool|null $isTransactionPending
     *
     * @dataProvider payDataProvider
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute($canCapture, $isOnline, $isGateway, $isTransactionPending)
    {
        $this->invoiceMock->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->invoiceItemMock]);

        if ($canCapture) {
            $this->invoiceMock->expects($this->any())
                ->method('getState')
                ->willReturn(\Magento\Sales\Model\Order\Invoice::STATE_OPEN);

            $this->orderPaymentMock->expects($this->any())
                ->method('canCapture')
                ->willReturn(true);

            if ($isOnline) {
                $this->invoiceMock->expects($this->once())
                    ->method('capture');
            } else {
                $this->invoiceMock->expects($this->never())
                    ->method('capture');

                $this->invoiceMock->expects($this->once())
                    ->method('setCanVoidFlag')
                    ->with(false);

                $this->invoiceMock->expects($this->once())
                    ->method('pay');
            }
        } else {
            $this->paymentMethodMock->expects($this->any())
                ->method('isGateway')
                ->willReturn($isGateway);

            $this->orderPaymentMock->expects($this->any())
                ->method('getIsTransactionPending')
                ->willReturn($isTransactionPending);

            $this->invoiceMock->expects($this->never())
                ->method('capture');

            if ((!$isGateway || !$isOnline) && !$isTransactionPending) {
                $this->invoiceMock->expects($this->once())
                    ->method('setCanVoidFlag')
                    ->with(false);

                $this->invoiceMock->expects($this->once())
                    ->method('pay');
            }
        }

        $this->orderMock->expects($this->once())
            ->method('setTotalInvoiced')
            ->with(86);
        $this->orderMock->expects($this->once())
            ->method('setBaseTotalInvoiced')
            ->with(86);
        $this->orderMock->expects($this->once())
            ->method('setSubtotalInvoiced')
            ->with(44);
        $this->orderMock->expects($this->once())
            ->method('setBaseSubtotalInvoiced')
            ->with(44);
        $this->orderMock->expects($this->once())
            ->method('setTaxInvoiced')
            ->with(30);
        $this->orderMock->expects($this->once())
            ->method('setBaseTaxInvoiced')
            ->with(30);
        $this->orderMock->expects($this->once())
            ->method('setDiscountTaxCompensationInvoiced')
            ->with(22);
        $this->orderMock->expects($this->once())
            ->method('setBaseDiscountTaxCompensationInvoiced')
            ->with(22);
        $this->orderMock->expects($this->once())
            ->method('setShippingTaxInvoiced')
            ->with(24);
        $this->orderMock->expects($this->once())
            ->method('setBaseShippingTaxInvoiced')
            ->with(24);
        $this->orderMock->expects($this->once())
            ->method('setShippingInvoiced')
            ->with(56);
        $this->orderMock->expects($this->once())
            ->method('setBaseShippingInvoiced')
            ->with(56);
        $this->orderMock->expects($this->once())
            ->method('setDiscountInvoiced')
            ->with(38);
        $this->orderMock->expects($this->once())
            ->method('setBaseDiscountInvoiced')
            ->with(38);
        $this->orderMock->expects($this->once())
            ->method('setBaseTotalInvoicedCost')
            ->with(62);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'sales_order_invoice_register',
                [
                    'invoice' => $this->invoiceMock,
                    'order' => $this->orderMock,
                ]
            );

        $this->assertEquals(
            $this->orderMock,
            $this->subject->execute(
                $this->orderMock,
                $this->invoiceMock,
                $isOnline
            )
        );
    }

    /**
     * @return array
     */
    public function payDataProvider()
    {
        return [
            'Invoice can capture, online' => [
                true, true, null, null,
            ],
            'Invoice can capture, offline' => [
                true, false, null, null,
            ],
            'Invoice can not capture, online, is not gateway, transaction is not pending' => [
                false, true, false, false,
            ],
            'Invoice can not capture, offline, gateway, transaction is not pending' => [
                false, false, true, false,
            ],
        ];
    }
}
