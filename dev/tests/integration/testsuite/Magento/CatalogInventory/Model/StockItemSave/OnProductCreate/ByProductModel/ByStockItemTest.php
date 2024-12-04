<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductCreate\ByProductModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 */
class ByStockItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

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
    private $productData = [
        ProductInterface::TYPE_ID => Type::TYPE_SIMPLE,
        'website_ids' => [1],
        ProductInterface::NAME => 'simpleByStockItemTest',
        ProductInterface::SKU => 'simpleByStockItemTest',
        ProductInterface::PRICE => 100,
        ProductInterface::EXTENSION_ATTRIBUTES_KEY => [],
    ];

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
        $this->productFactory = $objectManager->get(ProductInterfaceFactory::class);
        $this->stockItemFactory = $objectManager->get(StockItemInterfaceFactory::class);
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);

        /** @var CategorySetup $installer */
        $installer = $objectManager->create(CategorySetup::class);
        $attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
        $this->productData[ProductInterface::ATTRIBUTE_SET_ID] = $attributeSetId;
    }

    /**
     * Test saving of stock item by product data via product model (deprecated)
     */
    public function testSave()
    {
        /** @var Product $product */
        $product = $this->productFactory->create();
        $productData = $this->productData;
        $productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY]['stock_item'] = $this->stockItemData;
        $this->dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simpleByStockItemTest', $this->stockItemData);
    }

    /**
     * Test saving of manually created stock item (and set by extension attributes object) on product save via product
     * model (deprecated)
     */
    public function testSaveManuallyCreatedStockItem()
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemFactory->create();
        $this->dataObjectHelper->populateWithArray($stockItem, $this->stockItemData, StockItemInterface::class);

        /** @var Product $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product->getExtensionAttributes()->setStockItem($stockItem);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simpleByStockItemTest', $this->stockItemData);
    }

    public function testAutomaticIsInStockUpdate(): void
    {
        $stockItemData = [
            StockItemInterface::QTY => 0,
            StockItemInterface::IS_IN_STOCK => true,
            StockItemInterface::MANAGE_STOCK => 1,
        ];
        $expected = [
            StockItemInterface::QTY => 0,
            StockItemInterface::IS_IN_STOCK => false,
            StockItemInterface::STOCK_STATUS_CHANGED_AUTO => true,
        ];
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemFactory->create();
        $this->dataObjectHelper->populateWithArray($stockItem, $stockItemData, StockItemInterface::class);

        /** @var Product $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product->getExtensionAttributes()->setStockItem($stockItem);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simpleByStockItemTest', $expected);
    }
}
