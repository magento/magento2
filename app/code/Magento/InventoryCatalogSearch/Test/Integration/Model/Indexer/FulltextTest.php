<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogSearch\Model\Indexer\Fulltext as CatalogSearchIndexer;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/assign_products_to_websites.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
 * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
 * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
 *
 * @magentoDbIsolation disabled
 */
class FulltextTest extends TestCase
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var IndexerInterface
     */
    private $indexer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();

        $this->queryFactory = $objectManager->get(QueryFactory::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->indexer = $objectManager->get(Indexer::class);
        $this->indexer->load(CatalogSearchIndexer::INDEXER_ID);

        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();
    }

    /**
     * @param string $queryText
     * @param string $store
     * @param int $expectedSize
     * @return void
     *
     * @dataProvider searchPerStockDataProvider
     */
    public function testSearchPerStock(string $queryText, string $store, int $expectedSize)
    {
        $this->storeManager->setCurrentStore($store);
        $this->indexer->reindexAll();

        $products = $this->search($queryText);

        $this->assertCount($expectedSize, $products);
    }

    /**
     * @return array
     */
    public function searchPerStockDataProvider(): array
    {
        return [
            ['Orange', 'store_for_eu_website', 1],
            ['Orange', 'store_for_us_website', 0],
            ['Orange', 'store_for_global_website', 1],

            ['Blue', 'store_for_eu_website', 0],
            ['Blue', 'store_for_us_website', 1],
            ['Blue', 'store_for_global_website', 1],

            ['White', 'store_for_eu_website', 0],
            ['White', 'store_for_us_website', 0],
            ['White', 'store_for_global_website', 0],
        ];
    }

    /**
     * Search the text and return result collection.
     *
     * @param string $text
     * @return ProductInterface[]
     */
    private function search(string $text): array
    {
        $query = $this->queryFactory->create();
        $query->setQueryText($text);
        $query->saveIncrementalPopularity();

        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(
            Collection::class,
            [
                'searchRequestName' => 'quick_search_container'
            ]
        );
        $products = $collection
            ->addSearchFilter($text)
            ->getItems();
        return $products;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }

        parent::tearDown();
    }
}
