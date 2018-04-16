<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySkuInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\GetSalableQuantityDataBySku;
use Magento\InventoryConfigurableProduct\Model\GetQuantityInformationPerSource;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class SourceDeductionForVirtualProductsOnDefaultStockTest extends TestCase
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
     * @var GetSourceItemsBySkuInterface
     */
    private $getSourceItemBySku;

    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    private $invoiceItemCreationFactory;

    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    protected function setUp()
    {
        $this->invoiceOrder = Bootstrap::getObjectManager()->get(InvoiceOrderInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->getSourceItemBySku = Bootstrap::getObjectManager()->get(GetSourceItemsBySkuInterface::class);
        $this->invoiceItemCreationFactory = Bootstrap::getObjectManager()->get(InvoiceItemCreationInterfaceFactory::class);
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testSourceDeductionWhileInvoicingWholeOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'test_order_virt_1')
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

        $defaultStockId = $this->defaultStockProvider->getId();

        /** @var SourceItemInterface $sourceItem */
        $sourceItem = current($this->getSourceItemBySku->execute('VIRT-1')->getItems());
        self::assertEquals(28, $sourceItem->getQuantity());

        $sourceItem = current($this->getSourceItemBySku->execute('VIRT-2')->getItems());
        self::assertEquals(24, $sourceItem->getQuantity());

        $salableQty = $this->getProductSalableQty->execute('VIRT-1', $defaultStockId);
        self::assertEquals(28, $salableQty);

        $salableQty = $this->getProductSalableQty->execute('VIRT-2', $defaultStockId);
        self::assertEquals(24, $salableQty);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testSourceDeductionWhileInvoicingPartialOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'test_order_virt_1')
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

        $defaultStockId = $this->defaultStockProvider->getId();

        /** @var SourceItemInterface $sourceItem */
        $sourceItem = current($this->getSourceItemBySku->execute('VIRT-1')->getItems());
        self::assertEquals(30, $sourceItem->getQuantity());

        $sourceItem = current($this->getSourceItemBySku->execute('VIRT-2')->getItems());
        self::assertEquals(27, $sourceItem->getQuantity());

        $salableQty = $this->getProductSalableQty->execute('VIRT-1', $defaultStockId);
        self::assertEquals(28, $salableQty);

        $salableQty = $this->getProductSalableQty->execute('VIRT-2', $defaultStockId);
        self::assertEquals(24, $salableQty);
    }
}
