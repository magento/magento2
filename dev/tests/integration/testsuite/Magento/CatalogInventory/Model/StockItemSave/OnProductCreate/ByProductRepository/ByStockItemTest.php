<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductCreate\ByProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
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
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
        ProductInterface::NAME => 'simpleForByStockItemTest',
        ProductInterface::SKU => 'simpleForByStockItemTest',
        ProductInterface::PRICE => 100,
        ProductInterface::EXTENSION_ATTRIBUTES_KEY => [],
    ];

    /**
     * @var array
     */
    private $stockItemData = [
        StockItemInterface::QTY => 555,
        StockItemInterface::MANAGE_STOCK => true,
        StockItemInterface::IS_IN_STOCK => true,
    ];

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productFactory = $objectManager->get(ProductInterfaceFactory::class);
        $this->stockItemFactory = $objectManager->get(StockItemInterfaceFactory::class);
        $this->stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        // prevent internal caching in property
        $this->productRepository->cleanCache();
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);

        /** @var CategorySetup $installer */
        $installer = $objectManager->create(CategorySetup::class);
        $attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
        $this->productData[ProductInterface::ATTRIBUTE_SET_ID] = $attributeSetId;
    }

    /**
     * Test saving of stock item by product data via product repository
     */
    public function testSave()
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $productData = $this->productData;
        $productData[ProductInterface::EXTENSION_ATTRIBUTES_KEY]['stock_item'] = $this->stockItemData;
        $this->dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simpleForByStockItemTest', $this->stockItemData);
    }

    /**
     * Test saving of manually created stock item (and set by extension attributes object) on product save via product
     * repository
     */
    public function testSaveManuallyCreatedStockItem()
    {
        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockItemFactory->create();
        $this->dataObjectHelper->populateWithArray($stockItem, $this->stockItemData, StockItemInterface::class);

        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product->getExtensionAttributes()->setStockItem($stockItem);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simpleForByStockItemTest', $this->stockItemData);
    }

    /**
     * Test automatically stock item creating on product save via product repository
     */
    public function testAutomaticallyStockItemCreating()
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product = $this->productRepository->save($product);

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->dataObjectHelper->populateWithArray($stockItem, $this->stockItemData, StockItemInterface::class);
        $this->stockItemRepository->save($stockItem);

        $this->stockItemDataChecker->checkStockItemData('simpleForByStockItemTest', $this->stockItemData);
    }
}
