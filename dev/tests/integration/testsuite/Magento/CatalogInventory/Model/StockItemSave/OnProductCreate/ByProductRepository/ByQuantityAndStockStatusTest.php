<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductCreate\ByProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDbIsolation enabled
 */
class ByQuantityAndStockStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

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
        ProductInterface::NAME => 'simpleForQuantityAndStockStatus',
        ProductInterface::SKU => 'simpleForQuantityAndStockStatus',
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

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->productFactory = $objectManager->get(ProductInterfaceFactory::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        // prevent internal caching in property
        $this->productRepository->cleanCache();
        $this->dataObjectHelper = $objectManager->get(DataObjectHelper::class);
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);

        /** @var CategorySetup $installer */
        $installer = $objectManager->get(CategorySetup::class);
        $attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
        $this->productData[ProductInterface::ATTRIBUTE_SET_ID] = $attributeSetId;
    }

    /**
     * Test saving of stock item on product save by 'setQuantityAndStockStatus' method (deprecated) via product
     * repository
     */
    public function testSaveBySetQuantityAndStockStatus()
    {
        /** @var Product $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product->setQuantityAndStockStatus($this->stockItemData);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simpleForQuantityAndStockStatus', $this->stockItemData);
    }

    /**
     * Test saving of stock item on product save by 'setData' method with 'quantity_and_stock_status' key (deprecated)
     * via product repository
     */
    public function testSaveBySetData()
    {
        /** @var Product $product */
        $product = $this->productFactory->create();
        $this->dataObjectHelper->populateWithArray($product, $this->productData, ProductInterface::class);
        $product->setData('quantity_and_stock_status', $this->stockItemData);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simpleForQuantityAndStockStatus', $this->stockItemData);
    }
}
