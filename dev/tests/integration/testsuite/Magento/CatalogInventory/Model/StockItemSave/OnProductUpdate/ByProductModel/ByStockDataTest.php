<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductUpdate\ByProductModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\TestFramework\Helper\Bootstrap;

class ByStockDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

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
        $this->stockItemDataChecker = $objectManager->get(StockItemDataChecker::class);
    }

    /**
     * Test saving of stock item on product save by 'setStockData' method (deprecated) via product
     * model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testSaveBySetStockData()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->setStockData($this->stockItemData);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of stock item on product save by 'setData' method with 'stock_data' key (deprecated)
     * via product model (deprecated)
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testSaveBySetData()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->setData('stock_data', $this->stockItemData);
        $product->save();

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }
}
