<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Helper\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Helper\Stock;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddStockStatusToProductsOnDefaultStockTest extends TestCase
{
    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockHelper = Bootstrap::getObjectManager()->get(Stock::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     */
    public function testAddStockStatusToProducts()
    {
        $productsData = [
            'SKU-1' => 1,
            'SKU-2' => 1,
            'SKU-3' => 0,
        ];

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->addFieldToFilter(ProductInterface::SKU, ['in' => array_keys($productsData)]);
        $collection->load();
        self::assertCount(3, $collection->getItems());

        $this->stockHelper->addStockStatusToProducts($collection);

        /** @var ProductInterface $product */
        foreach ($collection as $product) {
            self::assertEquals($productsData[$product->getSku()], $product->isSalable());
        }
    }
}
