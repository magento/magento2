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
}
