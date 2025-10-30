<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\StockItemSave\OnProductUpdate\ByProductRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockItemSave\StockItemDataChecker;
use Magento\TestFramework\Helper\Bootstrap;

class ByQuantityAndStockStatusTest extends \PHPUnit\Framework\TestCase
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
     * Test saving of stock item on product save by 'setQuantityAndStockStatus' method (deprecated) via product
     * repository
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testSaveBySetQuantityAndStockStatus()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->setQuantityAndStockStatus($this->stockItemData);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }

    /**
     * Test saving of stock item on product save by 'setData' method with 'quantity_and_stock_status' key (deprecated)
     * via product repository
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation enabled
     */
    public function testSaveBySetData()
    {
        /** @var Product $product */
        $product = $this->productRepository->get('simple', false, null, true);
        $product->setData('quantity_and_stock_status', $this->stockItemData);
        $this->productRepository->save($product);

        $this->stockItemDataChecker->checkStockItemData('simple', $this->stockItemData);
    }
}
