<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Link;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddInStockFilterToCollectionTest extends TestCase
{

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @var Link
     */
    private $productLink;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp()
    {
        parent::setUp();

        $this->productLink = Bootstrap::getObjectManager()->get(Link::class);
        $this->stockHelper = Bootstrap::getObjectManager()->get(Stock::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @dataProvider addInStockFilterToCollectionDataProvider
     * @param string $storeCode
     * @param array $productsData
     */
    public function testAddInStockFilterToCollectionMangeStock($storeCode, $productsData)
    {
        $this->storeManager->setCurrentStore($storeCode);

        $product = $this->productRepository->get('SKU-1');

        $collection = $this->productLink->useUpSellLinks()->getProductCollection();
        $collection->setProduct($product);
        $collection->addAttributeToSelect('*');

        $this->stockHelper->addInStockFilterToCollection($collection);
        $this->assertEquals($productsData['count'], $collection->count());
    }

    /**
     * Data provider for testing product link count.
     *
     * @return array
     */
    public function addInStockFilterToCollectionDataProvider(): array
    {
        return [
            'eu_website' => [
                'store_for_eu_website',
                [
                    'count' => 0
                ],
            ],
            'us_website' => [
                'store_for_us_website',
                [
                    'count' => 1
                ],
            ],
            'global_website' => [
                'store_for_global_website',
                [
                    'count' => 1
                ],
            ],
        ];
    }
}
