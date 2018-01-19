<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test add stock status to select on not default website.
 */
class AddStockStatusToSelectWithNotDefaultStockTest extends TestCase
{
    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null|string
     */
    private $storeCodeBefore = null;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->create(StockStatus::class);
        $this->website = Bootstrap::getObjectManager()->create(Website::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_link.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_link.php
     *
     * @param string $store
     * @param int $expectedIsSalableCount
     * @param int $expectedNotSalableCount
     *
     * @dataProvider addStockStatusToSelectDataProvider
     */
    public function testAddStockStatusToSelect(string $store, int $expectedIsSalableCount, int $expectedNotSalableCount)
    {
        $this->storeManager->setCurrentStore($store);

        $actualIsSalableCount = $actualNotSalableCount = 0;

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->stockStatus->addStockStatusToSelect($collection->getSelect(), $this->website);

        foreach ($collection as $item) {
            $item->getIsSalable() == 1 ? $actualIsSalableCount++ : $actualNotSalableCount++;
        }

        self::assertEquals($expectedIsSalableCount, $actualIsSalableCount);
        self::assertEquals($expectedNotSalableCount, $actualNotSalableCount);
        self::assertEquals($expectedNotSalableCount + $expectedIsSalableCount, $collection->getSize());
    }

    /**
     * Data provider for testAddStockStatusToSelect().
     *
     * @return array
     */
    public function addStockStatusToSelectDataProvider(): array
    {
        return [
            ['store_for_eu_website', 1, 2],
            ['store_for_us_website', 1, 2],
            ['store_for_global_website', 2, 1],
        ];
    }
}
