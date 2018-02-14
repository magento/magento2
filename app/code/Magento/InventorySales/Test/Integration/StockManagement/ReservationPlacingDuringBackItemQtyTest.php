<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Test\Integration\StockManagement;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\InventoryApi\Api\StockRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

class ReservationPlacingDuringBackItemQtyTest extends TestCase
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var StockManagement
     */
    private $stockManagement;

    protected function setUp()
    {
        $this->getProductSalableQty = Bootstrap::getObjectManager()->get(GetProductSalableQtyInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->stockRepository = Bootstrap::getObjectManager()->get(StockRepositoryInterface::class);
        $this->websiteRepository = Bootstrap::getObjectManager()->get(WebsiteRepositoryInterface::class);
        $this->stockManagement = Bootstrap::getObjectManager()->get(StockManagement::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     */
    public function testRevertProductsSale()
    {
        self::assertEquals(8.5, $this->getProductSalableQty->execute('SKU-1', 10));

        $product = $this->productRepository->get('SKU-1');
        $website = $this->websiteRepository->get('eu_website');
        $this->stockManagement->backItemQty($product->getId(), 3.5, $website->getId());

        self::assertEquals(12, $this->getProductSalableQty->execute('SKU-1', 10));
    }
}
