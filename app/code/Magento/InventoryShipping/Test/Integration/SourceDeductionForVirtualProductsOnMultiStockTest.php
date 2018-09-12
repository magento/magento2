<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceDeductionForVirtualProductsOnMultiStockTest extends TestCase
{
    /**
     * @var InvoiceOrderInterface
     */
    private $invoiceOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    private $invoiceItemCreationFactory;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    protected function setUp()
    {
        $this->invoiceOrder = Bootstrap::getObjectManager()->get(InvoiceOrderInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->invoiceItemCreationFactory
            = Bootstrap::getObjectManager()->get(InvoiceItemCreationInterfaceFactory::class);
        $this->getReservationsQuantity = Bootstrap::getObjectManager()->get(GetReservationsQuantityInterface::class);
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_multi_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     *
     * @magentoDbIsolation disabled
     */
    public function testSourceDeductionWhileInvoicingWholeOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        /** @var OrderInterface $order */
        $order = current($this->orderRepository->getList($searchCriteria)->getItems());

        $invoiceItems = [];
        foreach ($order->getItems() as $orderItem) {
            /** @var InvoiceItemCreationInterface $invoiceItemCreation */
            $invoiceItemCreation = $this->invoiceItemCreationFactory->create();
            $invoiceItemCreation->setOrderItemId($orderItem->getItemId());
            $invoiceItemCreation->setQty($orderItem->getQtyOrdered());
            $invoiceItems[] = $invoiceItemCreation;
        }

        $this->invoiceOrder->execute($order->getEntityId(), false, $invoiceItems);

        self::assertEquals(0, $this->getSourceItemQuantity('VIRT-1', 'eu-1'));
        self::assertEquals(9, $this->getSourceItemQuantity('VIRT-1', 'eu-2'));

        self::assertEquals(16, $this->getSourceItemQuantity('VIRT-2', 'eu-1'));
        self::assertEquals(12, $this->getSourceItemQuantity('VIRT-2', 'eu-2'));

        self::assertEquals(0, $this->getReservationsQuantity('VIRT-1', 10));
        self::assertEquals(0, $this->getReservationsQuantity('VIRT-1', 10));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_multi_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_eu_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     *
     * @magentoDbIsolation disabled
     */
    public function testSourceDeductionWhileInvoicingPartialOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        /** @var OrderInterface $order */
        $order = current($this->orderRepository->getList($searchCriteria)->getItems());

        $invoiceItems = [];
        $orderItems = $order->getItems();

        // pick first order item
        $orderItem = current($orderItems);
        /** @var InvoiceItemCreationInterface $invoiceItemCreation */
        $invoiceItemCreation = $this->invoiceItemCreationFactory->create();
        $invoiceItemCreation->setOrderItemId($orderItem->getItemId());
        $invoiceItemCreation->setQty(3);
        $invoiceItems[] = $invoiceItemCreation;

        // pick second order item
        next($orderItems);
        $orderItem = current($orderItems);
        /** @var InvoiceItemCreationInterface $invoiceItemCreation */
        $invoiceItemCreation = $this->invoiceItemCreationFactory->create();
        $invoiceItemCreation->setOrderItemId($orderItem->getItemId());
        $invoiceItemCreation->setQty(3);
        $invoiceItems[] = $invoiceItemCreation;

        $this->invoiceOrder->execute($order->getEntityId(), false, $invoiceItems);

        self::assertEquals(0, $this->getSourceItemQuantity('VIRT-1', 'eu-1'));
        self::assertEquals(11, $this->getSourceItemQuantity('VIRT-1', 'eu-2'));

        self::assertEquals(19, $this->getSourceItemQuantity('VIRT-2', 'eu-1'));
        self::assertEquals(12, $this->getSourceItemQuantity('VIRT-2', 'eu-2'));

        self::assertEquals(-2, $this->getReservationsQuantity('VIRT-1', 10));
        self::assertEquals(-3, $this->getReservationsQuantity('VIRT-2', 10));

        self::assertEquals(9, $this->getSalableQty('VIRT-1', 10));
        self::assertEquals(28, $this->getSalableQty('VIRT-2', 10));
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return float
     */
    private function getSourceItemQuantity(string $sku, string $sourceCode): float
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', $sku)
            ->addFilter('source_code', $sourceCode)
            ->create();
        /** @var SourceItemInterface $sourceItem */
        $sourceItem = current($this->sourceItemRepository->getList($searchCriteria)->getItems());
        return (float)$sourceItem->getQuantity();
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    private function getReservationsQuantity(string $sku, int $stockId): float
    {
        return $this->getReservationsQuantity->execute($sku, $stockId);
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return float
     */
    private function getSalableQty(string $sku, int $stockId): float
    {
        return $this->getProductSalableQty->execute($sku, $stockId);
    }
}
