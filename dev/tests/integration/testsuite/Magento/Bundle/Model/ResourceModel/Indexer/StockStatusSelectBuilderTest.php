<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for StockStatusSelectBuilder indexer resource model.
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class StockStatusSelectBuilderTest extends TestCase
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Status
     */
    private $stockStatus;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->productCollectionFactory = Bootstrap::getObjectManager()->create(CollectionFactory::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
        $this->stockStatus = Bootstrap::getObjectManager()->create(Status::class);
    }

    /**
     * Test bundle product without options will be returned in case "isFilterInStock" set to false.
     *
     * @magentoDataFixture Magento/Bundle/_files/empty_bundle_product.php
     * @dataProvider buildSelectDataProvider
     * @param bool $isFilterInStock
     * @param int $resultCount
     * @return void
     */
    public function testCollectionResults(bool $isFilterInStock, int $resultCount)
    {
        $productCollection = $this->productCollectionFactory->create();
        $this->stockStatus->addStockDataToCollection($productCollection, $isFilterInStock);

        $this->assertEquals($resultCount, $productCollection->getSize());
    }

    /**
     * Test bundle product without options is present in cataloginventory_stock_status table.
     *
     * @magentoDataFixture Magento/Bundle/_files/empty_bundle_product.php
     * @return void
     */
    public function testIndexTable()
    {
        $product = $this->productRepository->get('bundle-product');
        $stockStatuses = $this->stockStatus->getProductsStockStatuses([$product->getId()], 0);

        $this->assertEquals([$product->getId() => 0], $stockStatuses);
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
