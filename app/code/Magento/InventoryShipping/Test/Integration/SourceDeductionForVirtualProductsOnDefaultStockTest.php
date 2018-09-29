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
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
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
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var InvoiceItemCreationInterfaceFactory
     */
    private $invoiceItemCreationFactory;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    protected function setUp()
    {
        $this->invoiceOrder = Bootstrap::getObjectManager()->get(InvoiceOrderInterface::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
        $this->sourceItemRepository = Bootstrap::getObjectManager()->get(SourceItemRepositoryInterface::class);
        $this->invoiceItemCreationFactory
            = Bootstrap::getObjectManager()->get(InvoiceItemCreationInterfaceFactory::class);
        $this->defaultStockProvider = Bootstrap::getObjectManager()->get(DefaultStockProviderInterface::class);
        $this->defaultSourceProvider = Bootstrap::getObjectManager()->get(DefaultSourceProviderInterface::class);
        $this->getReservationsQuantity = Bootstrap::getObjectManager()->get(GetReservationsQuantityInterface::class);
        $this->invoiceRepository = Bootstrap::getObjectManager()->get(InvoiceRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_default_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
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

        $this->invoiceOrder->execute($order->getEntityId(), true, $invoiceItems);

        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        self::assertEquals(28, $this->getSourceItemQuantity('VIRT-1', $defaultSourceCode));
        self::assertEquals(24, $this->getSourceItemQuantity('VIRT-2', $defaultSourceCode));

        self::assertEquals(0, $this->getReservationsQuantity('VIRT-1', $defaultStockId));
        self::assertEquals(0, $this->getReservationsQuantity('VIRT-2', $defaultStockId));
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/products_virtual.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/source_items_for_virtual_on_default_source.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/create_quote_on_default_website.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryShipping/Test/_files/order_virtual_products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     */
    public function testSourceDeductionWhileInvoicingPartialOrderedQty()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', 'created_order_for_test')
            ->create();
        /** @var OrderInterface $order */
        $order = current($this->orderRepository->getList($searchCriteria)->getItems());

        $invoiceItems = [];
        $qtyToInvoice = [
            'VIRT-1' => 3,
            'VIRT-2' => 2,
        ];

        foreach ($order->getItems() as $orderItem) {
            if (isset($qtyToInvoice[$orderItem->getSku()])) {
                /** @var InvoiceItemCreationInterface $invoiceItemCreation */
                $invoiceItemCreation = $this->invoiceItemCreationFactory->create();
                $invoiceItemCreation->setOrderItemId($orderItem->getItemId());
                $invoiceItemCreation->setQty($qtyToInvoice[$orderItem->getSku()]);
                $invoiceItems[] = $invoiceItemCreation;
            }
        }

        $this->invoiceOrder->execute($order->getEntityId(), false, $invoiceItems);

        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        self::assertEquals(30, $this->getSourceItemQuantity('VIRT-1', $defaultSourceCode));
        self::assertEquals(28, $this->getSourceItemQuantity('VIRT-2', $defaultSourceCode));

        self::assertEquals(-2, $this->getReservationsQuantity('VIRT-1', $defaultStockId));
        self::assertEquals(-4, $this->getReservationsQuantity('VIRT-2', $defaultStockId));
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return float
     */
    private function getSourceItemQuantity($sku, $sourceCode)
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
    private function getReservationsQuantity($sku, $stockId)
    {
        return $this->getReservationsQuantity->execute($sku, $stockId);
    }
}
