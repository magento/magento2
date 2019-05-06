<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Tests \Magento\Sales\Model\Service\InvoiceService
 */
class InvoiceServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->invoiceService = Bootstrap::getObjectManager()->create(InvoiceService::class);
    }

    /**
     * @param int $invoiceQty
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     * @return void
     * @dataProvider prepareInvoiceConfigurableProductDataProvider
     */
    public function testPrepareInvoiceConfigurableProduct(int $invoiceQty): void
    {
        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)->load('100000001', 'increment_id');
        $orderItems = $order->getItems();
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItemId()) {
                $parentItemId = $orderItem->getParentItemId();
            }
        }
        $invoice = $this->invoiceService->prepareInvoice($order, [$parentItemId => $invoiceQty]);
        $invoiceItems = $invoice->getItems();
        foreach ($invoiceItems as $invoiceItem) {
            $this->assertEquals($invoiceQty, $invoiceItem->getQty());
        }
    }

    public function prepareInvoiceConfigurableProductDataProvider()
    {
        return [
            'full invoice' => [2],
            'partial invoice' => [1]
        ];
    }

    /**
     * @param int $invoiceQty
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @return void
     * @dataProvider prepareInvoiceSimpleProductDataProvider
     */
    public function testPrepareInvoiceSimpleProduct(int $invoiceQty): void
    {
        /** @var OrderInterface $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)->load('100000001', 'increment_id');
        $orderItems = $order->getItems();
        $invoiceQtys = [];
        foreach ($orderItems as $orderItem) {
            $invoiceQtys[$orderItem->getItemId()] = $invoiceQty;
        }
        $invoice = $this->invoiceService->prepareInvoice($order, $invoiceQtys);
        $invoiceItems = $invoice->getItems();
        foreach ($invoiceItems as $invoiceItem) {
            $this->assertEquals($invoiceQty, $invoiceItem->getQty());
        }
    }

    public function prepareInvoiceSimpleProductDataProvider()
    {
        return [
            'full invoice' => [2],
            'partial invoice' => [1]
        ];
    }

    /**
     * Checks if ordered and invoiced qty of bundle product does match.
     *
     * @param array $qtyToInvoice
     * @param array $qtyInvoiced
     * @param string $errorMsg
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @magentoDataFixture Magento/Sales/_files/order_with_bundle.php
     * @dataProvider bundleProductQtyOrderedDataProvider
     */
    public function testPrepareInvoiceBundleProduct(
        array $qtyToInvoice,
        array $qtyInvoiced,
        string $errorMsg
    ): void {
        /** @var Order $order */
        $order = Bootstrap::getObjectManager()->create(Order::class)
            ->load('100000001', 'increment_id');

        $predefinedQtyToInvoice = $this->getPredefinedQtyToInvoice($order, $qtyToInvoice);
        $invoice = $this->invoiceService->prepareInvoice($order, $predefinedQtyToInvoice);

        foreach ($invoice->getItems() as $invoiceItem) {
            if (isset($qtyInvoiced[$invoiceItem->getSku()])) {
                $this->assertEquals(
                    $qtyInvoiced[$invoiceItem->getSku()],
                    $invoiceItem->getQty(),
                    sprintf($errorMsg, $invoiceItem->getSku())
                );
            }
        }
    }

    /**
     * Data provider for invoice creation with and w/o predefined qty to invoice.
     *
     * @return array
     */
    public function bundleProductQtyOrderedDataProvider(): array
    {
        return [
            'Create invoice w/o predefined qty' => [
                'Qty to invoice' => [],
                'Qty ordered' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'Error msg' => 'Invoiced qty for product %s does not match.',
            ],
            'Create invoice with predefined qty' => [
                'Qty to invoice' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'Qty ordered' => [
                    'bundle_1' => 2,
                    'bundle_simple_1' => 10,
                ],
                'Error msg' => 'Invoiced qty for product %s does not match.',
            ],
            'Create invoice with partial predefined qty for bundle' => [
                'Qty to invoice' => [
                    'bundle_1' => 1,
                ],
                'Qty ordered' => [
                    'bundle_1' => 1,
                    'bundle_simple_1' => 5,
                ],
                'Error msg' => 'Invoiced qty for product %s does not match.',
            ],
        ];
    }

    /**
     * Associate product qty to invoice to order item id.
     *
     * @param Order $order
     * @param array $qtyToInvoice
     * @return array
     */
    private function getPredefinedQtyToInvoice(Order $order, array $qtyToInvoice): array
    {
        $predefinedQtyToInvoice = [];

        foreach ($order->getAllItems() as $orderItem) {
            if (array_key_exists($orderItem->getSku(), $qtyToInvoice)) {
                $predefinedQtyToInvoice[$orderItem->getId()] = $qtyToInvoice[$orderItem->getSku()];
            }
        }

        return $predefinedQtyToInvoice;
    }
}
