<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\Shipping;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shipping
     */
    private $total;

    protected function setUp()
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
     * @return \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createInvoiceStub(array $prevInvoicesData, $orderShipping)
    {
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order->expects($this->any())
            ->method('getInvoiceCollection')
            ->will($this->returnValue($this->getInvoiceCollection($prevInvoicesData)));
        $order->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn($orderShipping);
        /** @var $invoice \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject */
        $invoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
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
     * @return \Magento\Framework\Data\Collection
     */
    private function getInvoiceCollection(array $invoicesData)
    {
        $className = 'Magento\Sales\Model\Order\Invoice';
        $result = new \Magento\Framework\Data\Collection(
            $this->getMock('Magento\Framework\Data\Collection\EntityFactory', [], [], '', false)
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'orderFactory' => $this->getMock('Magento\Sales\Model\OrderFactory', [], [], '', false),
            'orderResourceFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\OrderFactory',
                [],
                [],
                '',
                false
            ),
            'calculatorFactory' => $this->getMock(
                \Magento\Framework\Math\CalculatorFactory::class,
                [],
                [],
                '',
                false
            ),
            'invoiceItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory',
                [],
                [],
                '',
                false
            ),
            'invoiceCommentFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Invoice\CommentFactory',
                [],
                [],
                '',
                false
            ),
            'commentCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory',
                [],
                [],
                '',
                false
            ),
        ];
        foreach ($invoicesData as $oneInvoiceData) {
            $arguments['data'] = $oneInvoiceData;
            $arguments = $objectManagerHelper->getConstructArguments($className, $arguments);
            /** @var $prevInvoice \Magento\Sales\Model\Order\Invoice */
            $prevInvoice = $this->getMock($className, ['_init'], $arguments);
            $result->addItem($prevInvoice);
        }
        return $result;
    }
}
