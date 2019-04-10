<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Test\Integration;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\InventoryCatalog\Model\GetDefaultSourceItemBySku;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class UpdateDefaultSourceItemAtLegacyStockItemSaveTest extends TestCase
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var GetDefaultSourceItemBySku
     */
    private $getDefaultSourceItemBySku;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    protected function setUp()
    {
        parent::setUp();

        $this->stockRegistry = Bootstrap::getObjectManager()->create(StockRegistryInterface::class);
        $this->getDefaultSourceItemBySku = Bootstrap::getObjectManager()->get(GetDefaultSourceItemBySku::class);
        $this->consumer = Bootstrap::getObjectManager()->create(ConsumerFactory::class)
            ->get('legacyInventorySynchronization', 100);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveLegacyStockItemAssignedToDefaultSource(): void
    {
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-1');
        $stockItem->setQty(10);
        $this->stockRegistry->updateStockItemBySku('SKU-1', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-1');
        self::assertEquals(
            10,
            $defaultSourceItem->getQuantity(),
            'Quantity is not updated in default source when legacy stock is updated and product was'
                . 'previously assigned to default source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoAdminConfigFixture cataloginventory/legacy_stock/async 1
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveLegacyStockItemAssignedToDefaultSourceAsynchronously(): void
    {
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-1');
        $stockItem->setQty(10);
        $this->stockRegistry->updateStockItemBySku('SKU-1', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-1');
        self::assertEquals(
            5.5,
            $defaultSourceItem->getQuantity(),
            'Source item was update synchronously even if asynchronous operation was requested'
        );

        $this->consumer->process(1);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-1');
        self::assertEquals(
            10,
            $defaultSourceItem->getQuantity(),
            'Asynchronous source item update failed'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveLegacyStockItemNotAssignedToDefaultSource(): void
    {
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-2');
        $stockItem->setQty(10);
        $this->stockRegistry->updateStockItemBySku('SKU-2', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-2');
        self::assertEquals(
            10,
            $defaultSourceItem->getQuantity(),
            'Quantity is not updated in default source when legacy stock is updated'
        );

        // SKU-3 is out of stock and not assigned to default source
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-3');
        $stockItem->setQty(10);
        $this->stockRegistry->updateStockItemBySku('SKU-3', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-3');
        self::assertEquals(
            10,
            $defaultSourceItem->getQuantity(),
            'Quantity is not updated in default source when legacy stock is updated and product was not '
                . 'previously assigned to default source'
        );
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDbIsolation enabled
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testSaveLegacyStockItemWithoutDefaultSourceAssignment(): void
    {
        // SKU-3 is out of stock and not assigned to default source
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-3');
        $stockItem->setQty(0);
        $stockItem->setIsInStock(false);
        $this->stockRegistry->updateStockItemBySku('SKU-3', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-3');
        self::assertNull(
            $defaultSourceItem,
            'Product is assigned to default source on legacy stock item save even if it should not be'
        );

        // SKU-5 is out of stock and not assigned to default source
        $stockItem = $this->stockRegistry->getStockItemBySku('SKU-5');
        $stockItem->setQty(1);
        $stockItem->setIsInStock(true);
        $this->stockRegistry->updateStockItemBySku('SKU-5', $stockItem);

        $defaultSourceItem = $this->getDefaultSourceItemBySku->execute('SKU-5');
        self::assertNotNull(
            $defaultSourceItem,
            'Product is not assigned to default source on legacy stock item save even if it should be'
        );
    }
}
