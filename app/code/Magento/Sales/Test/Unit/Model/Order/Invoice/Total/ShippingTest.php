<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Total;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\Math\CalculatorFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\CommentFactory;
use Magento\Sales\Model\Order\Invoice\Total\Shipping;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    private $total;

    protected function setUp(): void
    {
        $this->total = new Shipping();
    }

    /**
     * @dataProvider collectWithNoOrZeroPrevInvoiceDataProvider
     * @param array $prevInvoicesData
     * @param float $orderShipping
     * @param float $expectedShipping
     */
    public function testCollectWithNoOrZeroPrevInvoice(array $prevInvoicesData, $orderShipping, $expectedShipping)
    {
        $invoice = $this->createInvoiceStub($prevInvoicesData, $orderShipping);
        $invoice->expects($this->exactly(2))
            ->method('setShippingAmount')
            ->withConsecutive([0], [$expectedShipping]);

        $this->total->collect($invoice);
    }

    /**
     * @return array
     */
    public static function collectWithNoOrZeroPrevInvoiceDataProvider()
    {
        return [
            'no previous invoices' => [
                'prevInvoicesData' => [[]],
                'orderShipping' => 10.00,
                'expectedShipping' => 10.00,
            ],
            'zero shipping in previous invoices' => [
                'prevInvoicesData' => [['shipping_amount' => '0.0000']],
                'orderShipping' => 10.00,
                'expectedShipping' => 10.00,
            ],
        ];
    }

    public function testCollectWithPreviousInvoice()
    {
        $orderShipping = 10.00;
        $prevInvoicesData = [['shipping_amount' => '10.000']];
        $invoice = $this->createInvoiceStub($prevInvoicesData, $orderShipping);
        $invoice->expects($this->once())
            ->method('setShippingAmount')
            ->with(0);

        $this->total->collect($invoice);
    }

    /**
     * Create stub of an invoice
     *
     * @param array $prevInvoicesData
     * @param float $orderShipping
     * @return Invoice|MockObject
     */
    private function createInvoiceStub(array $prevInvoicesData, $orderShipping)
    {
        $order = $this->getMockBuilder(Order::class)
            ->setMethods(['getInvoiceCollection', 'getShippingAmount'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $order->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn($this->getInvoiceCollection($prevInvoicesData));
        $order->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn($orderShipping);
        /** @var \Magento\Sales\Model\Order\Invoice|MockObject $invoice */
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invoice->expects($this->any())
            ->method('getOrder')
            ->willReturn($order);
        return $invoice;
    }

    /**
     * Retrieve new invoice collection from an array of invoices' data
     *
     * @param array $invoicesData
     * @return Collection
     */
    private function getInvoiceCollection(array $invoicesData)
    {
        $className = Invoice::class;
        $result = new Collection(
            $this->createMock(EntityFactory::class)
        );
        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'orderFactory' => $this->createMock(OrderFactory::class),
            'orderResourceFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\OrderFactory::class
            ),
            'calculatorFactory' => $this->createMock(
                CalculatorFactory::class
            ),
            'invoiceItemCollectionFactory' => $this->createMock(
                CollectionFactory::class
            ),
            'invoiceCommentFactory' => $this->createMock(
                CommentFactory::class
            ),
            'commentCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory::class
            ),
        ];
        foreach ($invoicesData as $oneInvoiceData) {
            $arguments['data'] = $oneInvoiceData;
            $arguments = $objectManagerHelper->getConstructArguments($className, $arguments);
            /** @var \Magento\Sales\Model\Order\Invoice $prevInvoice */
            $prevInvoice = $this->getMockBuilder($className)
                ->setMethods(['_init'])
                ->setConstructorArgs($arguments)
                ->getMock();
            $result->addItem($prevInvoice);
        }
        return $result;
    }
}
