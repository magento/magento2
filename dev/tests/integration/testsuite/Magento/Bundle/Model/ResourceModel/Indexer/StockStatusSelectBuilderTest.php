<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for StockStatusSelectBuilder indexer resource model.
 */
class StockStatusSelectBuilderTest extends TestCase
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var Status
     */
    private $stockStatus;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->processor = Bootstrap::getObjectManager()->get(Processor::class);
        $this->productCollectionFactory = Bootstrap::getObjectManager()->create(CollectionFactory::class);
        $this->stockStatus = Bootstrap::getObjectManager()->create(Status::class);
    }

    /**
     * Check, bundle product without options will be returned in case "isFilterInStock" set to false.
     *
     * @magentoDataFixture Magento/Bundle/_files/empty_bundle_product.php
     * @dataProvider buildSelectDataProvider
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @param bool $isFilterInStock
     * @param int $resultCount
     * @return void
     */
    public function testBuildSelect(bool $isFilterInStock, int $resultCount): void
    {
        $this->processor->reindexAll();
        $productCollection = $this->productCollectionFactory->create();
        $this->stockStatus->addStockDataToCollection($productCollection, $isFilterInStock);

        $this->assertEquals($resultCount, $productCollection->getSize());
    }

    /**
     * Provide test data for testBuildSelect().
     *
     * @return array
     */
    public function buildSelectDataProvider(): array
    {
        return [
            [
                'is_filter_in_stock' => true,
                'resultCount' => 0,
            ],
            [
                'is_filter_in_stock' => false,
                'resultCount' => 1,
            ],
        ];
    }
}

