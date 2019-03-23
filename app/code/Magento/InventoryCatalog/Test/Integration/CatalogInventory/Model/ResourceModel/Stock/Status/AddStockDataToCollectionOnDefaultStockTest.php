<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AddStockDataToCollectionOnDefaultStockTest extends TestCase
{
    /**
     * @var StockStatus
     */
    private $stockStatus;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stockStatus = Bootstrap::getObjectManager()->create(StockStatus::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryCatalog/Test/_files/source_items_on_default_source.php
     *
     * @param int $expectedSize
     * @param bool $isFilterInStock
     * @return void
     *
     * @dataProvider addStockDataToCollectionDataProvider
     */
    public function testAddStockDataToCollection(int $expectedSize, bool $isFilterInStock)
    {
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection = $this->stockStatus->addStockDataToCollection($collection, $isFilterInStock);

        self::assertEquals($expectedSize, $collection->getSize());
    }

    /**
     * @return array
     */
    public function addStockDataToCollectionDataProvider(): array
    {
        return [
            [4, true],
            [6, false],
        ];
    }
}
