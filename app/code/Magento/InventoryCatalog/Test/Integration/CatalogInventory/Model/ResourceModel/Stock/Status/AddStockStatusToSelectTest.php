<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test add stock status to select
 */
class AddStockStatusToSelectTest extends TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->get(StockStatus::class);
        $this->website = Bootstrap::getObjectManager()->create(Website::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     *
     * @param string $websiteCode
     * @param int $expectedIsSalableCount
     * @param int $expectedNotSalableCount
     *
     * @dataProvider addStockStatusToSelectDataProvider
     *
     * @magentoDbIsolation disabled
     */
    public function testAddStockStatusToSelect(
        string $websiteCode,
        int $expectedIsSalableCount,
        int $expectedNotSalableCount
    ) {
        $actualIsSalableCount = $actualNotSalableCount = 0;

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->website->setCode($websiteCode);
        $this->stockStatus->addStockStatusToSelect($collection->getSelect(), $this->website);

        foreach ($collection as $item) {
            $item->getIsSalable() == 1 ? $actualIsSalableCount++ : $actualNotSalableCount++;
        }

        self::assertEquals($expectedIsSalableCount, $actualIsSalableCount);
        self::assertEquals($expectedNotSalableCount, $actualNotSalableCount);
        self::assertEquals($expectedNotSalableCount + $expectedIsSalableCount, $collection->getSize());
    }

    /**
     * @return array
     */
    public function addStockStatusToSelectDataProvider(): array
    {
        return [
            ['eu_website', 3, 3],
            ['us_website', 1, 5],
            ['global_website', 4, 2],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Website code is empty
     */
    public function testAddStockStatusToSelectWithEmptyWebsiteCode()
    {
        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->stockStatus->addStockStatusToSelect($collection->getSelect(), $this->website);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No linked stock found
     */
    public function testAddStockStatusToSelectWithNotExistedWebsiteCode()
    {
        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $this->website->setCode('not_existed_code');
        $this->stockStatus->addStockStatusToSelect($collection->getSelect(), $this->website);
    }
}
