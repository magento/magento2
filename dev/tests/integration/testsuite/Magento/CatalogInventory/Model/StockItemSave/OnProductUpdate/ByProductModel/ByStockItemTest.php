<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductUpdate\ByProductModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

class ByStockItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockItemInterfaceFactory
     */
    private $stockItemFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StockItemDataChecker
     */
    private $stockItemDataChecker;

    /**
     * @var array
     */
    private $stockItemData = [
        StockItemInterface::QTY => 555,
        StockItemInterface::MANAGE_STOCK => true,
        StockItemInterface::IS_IN_STOCK => false,
    ];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->stockItemFactory = $objectManager->get(StockItemInterfaceFactory::class);
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);
    }

    /**
     * Test saving of stock item by product data via product model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSave()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY]['stock_item'] = $this->stockItemData;
        $this->dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of manually created stock item (and set by extension attributes object) on product save via
     * product model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveManuallyCreatedStockItem()
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemFactory->create();
        $this->dataObjectHelper->populateWithArray($stockItem, $this->stockItemData, StockItemInterface::class);

        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->getExtensionAttributes()->setStockItem($stockItem);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of manually updated stock item (obtained from extension attributes object) on product save via
     * product repository (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveManuallyUpdatedStockItem()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $this->stockItemData,
            StockItemInterface::class
        );
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testAutomaticIsInStockUpdate(): void
    {
        // 1. Set qty to 0 and check that is_in_stock is updated automatically to false
        $this->updateStockDataAndCheck(
            [
                StockItemInterface::QTY => 0,
            ],
            [
                StockItemInterface::QTY => 0,
                StockItemInterface::IS_IN_STOCK => false,
                StockItemInterface::STOCK_STATUS_CHANGED_AUTO => true,
            ]
        );
        // 2. Set qty to 10 and check that is_in_stock is updated automatically to true
        $this->updateStockDataAndCheck(
            [
                StockItemInterface::QTY => 10,
            ],
            [
                StockItemInterface::QTY => 10,
                StockItemInterface::IS_IN_STOCK => true,
                StockItemInterface::STOCK_STATUS_CHANGED_AUTO => true,
            ]
        );
        // 3. Set is_in_stock to false and check that is_in_stock is set to false
        // and stock_status_changed_auto is set to false
        $this->updateStockDataAndCheck(
            [
                StockItemInterface::IS_IN_STOCK => false,
            ],
            [
                StockItemInterface::QTY => 10,
                StockItemInterface::IS_IN_STOCK => false,
                StockItemInterface::STOCK_STATUS_CHANGED_AUTO => false,
            ]
        );
        // 4. Set qty to 0 and check that is_in_stock is still false
        // and stock_status_changed_auto is also false
        $this->updateStockDataAndCheck(
            [
                StockItemInterface::QTY => 0,
            ],
            [
                StockItemInterface::QTY => 0,
                StockItemInterface::IS_IN_STOCK => false,
                StockItemInterface::STOCK_STATUS_CHANGED_AUTO => false,
            ]
        );
        // 5. Set qty to 10 and check that is_in_stock is still false
        // and stock_status_changed_auto is also false
        $this->updateStockDataAndCheck(
            [
                StockItemInterface::QTY => 10,
            ],
            [
                StockItemInterface::QTY => 10,
                StockItemInterface::IS_IN_STOCK => false,
                StockItemInterface::STOCK_STATUS_CHANGED_AUTO => false,
            ]
        );
    }

    /**
     * @param $dataToUpdate
     * @param $expectedData
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function updateStockDataAndCheck($dataToUpdate, $expectedData): void
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->dataObjectHelper->populateWithArray(
            $stockItem,
            $dataToUpdate,
            StockItemInterface::class
        );
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $expectedData);
    }
}
