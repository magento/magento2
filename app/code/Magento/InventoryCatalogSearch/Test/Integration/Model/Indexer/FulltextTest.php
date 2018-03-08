<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Test\Integration\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\Search\Request\Dimension;
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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FulltextTest extends TestCase
{
    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    private $indexer;

    /**
     * @var Fulltext
     */
    private $resourceFulltext;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterface
     */
    private $productSku1;

    /**
     * @var ProductInterface
     */
    private $productSku2;

    /**
     * @var ProductInterface
     */
    private $productSku3;

    /**
     * @var  Dimension
     */
    private $dimension;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storeCodeBefore;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Indexer\IndexerInterface indexer */
        $this->indexer = $objectManager->get(Indexer::class);
        $this->indexer->load(CatalogSearchIndexer::INDEXER_ID);

        $this->resourceFulltext = $objectManager->get(Fulltext::class);
        $this->queryFactory = $objectManager->get(QueryFactory::class);

        $this->dimension = $objectManager->create(
            Dimension::class,
            ['name' => 'scope', 'value' => '1']
        );

        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->storeCodeBefore = $this->storeManager->getStore()->getCode();

        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->productSku1 = $this->productRepository->get('SKU-1');
        $this->productSku2 = $this->productRepository->get('SKU-2');
        $this->productSku3 = $this->productRepository->get('SKU-3');
        $this->productAction = Bootstrap::getObjectManager()->get(Action::class);
    }

    /**
     * @param string $store
     * @param array $expectedSearchSize
     * @return void
     *
     * @dataProvider reindexAllDataProvider
     */
    public function testReindexAll(string $store, array $expectedSearchSize)
    {
        $this->storeManager->setCurrentStore($store);
        $this->indexer->reindexAll();

        $products = $this->search('Orange');
        $this->assertCount($expectedSearchSize[0], $products);
        if ($expectedSearchSize[0] > 0) {
            $this->assertEquals($this->productSku1->getId(), $products[0]->getId());
        }

        $products = $this->search('Simple Product');
        $this->assertCount($expectedSearchSize[1], $products);
    }

    /**
     * @return array
     */
    public function reindexAllDataProvider(): array
    {
        return [
            ['store_for_eu_website', [1, 1]],
            ['store_for_us_website', [0, 1]],
            ['store_for_global_website', [1, 2]],
        ];
    }

    /**
     * @param string $store
     * @param array $expectedSearchSize
     * @return void
     *
     * @dataProvider reindexRowDataProvider
     */
    public function testReindexRowAfterEdit(string $store, array $expectedSearchSize)
    {
        $this->storeManager->setCurrentStore($store);

        $this->indexer->reindexAll();

        $this->productSku1->setName('Simple Product Red');
        $this->productRepository->save($this->productSku1);

        $products = $this->search('Orange');
        $this->assertCount(0, $products);

        $products = $this->search('Red');
        $this->assertCount($expectedSearchSize[0], $products);
        if ($expectedSearchSize[0] > 0) {
            $this->assertEquals($this->productSku1->getId(), $products[0]->getId());
        }
        $products = $this->search('Simple Product');
        $this->assertCount($expectedSearchSize[1], $products);
    }

    /**
     * @return array
     */
    public function reindexRowDataProvider(): array
    {
        return [
            ['store_for_eu_website', [1, 1]],
            ['store_for_us_website', [0, 1]],
            ['store_for_global_website', [1, 2]],
        ];
    }

    /**
     * @param string $store
     * @return void
     *
     * @dataProvider reindexRowAfterMassActionDataProvider
     */
    public function testReindexRowAfterMassAction(string $store)
    {
        $this->storeManager->setCurrentStore($store);

        $this->indexer->reindexAll();

        $productIds = [
            $this->productSku1->getId(),
            $this->productSku2->getId(),
        ];
        $attrData = [
            'name' => 'Simple Product Common',
        ];

        $this->productAction->updateAttributes($productIds, $attrData, $this->storeManager->getStore()->getId());

        $products = $this->search('Orange');
        $this->assertCount(0, $products);

        $products = $this->search('Blue');
        $this->assertCount(0, $products);

        $products = $this->search('White');
        $this->assertCount(0, $products);
    }

    /**
     * @return array
     */
    public function reindexRowAfterMassActionDataProvider(): array
    {
        return [
            ['store_for_eu_website'],
            ['store_for_us_website'],
            ['store_for_global_website'],
        ];
    }

    /**
     * @magentoAppArea adminhtml

     * @param string $store
     * @param int $expectedSearchSize
     * @return void
     *
     * @dataProvider reindexRowAfterDeleteDataProvider
     */
    public function testReindexRowAfterDelete(string $store, int $expectedSearchSize)
    {
        $this->storeManager->setCurrentStore($store);
        $this->indexer->reindexAll();

        $this->productRepository->delete($this->productSku2);

        $products = $this->search('Simple Product');

        $this->assertCount($expectedSearchSize, $products);
    }

    /**
     * @return array
     */
    public function reindexRowAfterDeleteDataProvider(): array
    {
        return [
            ['store_for_eu_website', 1],
            ['store_for_us_website', 0],
            ['store_for_global_website', 1],
        ];
    }
    /**
     * Search the text and return result collection.
     *
     * @param string $text
     *
     * @return Product[]
     */
    private function search(string $text): array
    {
        $query = $this->queryFactory->create();
        $query->setQueryText($text);
        $query->saveIncrementalPopularity();
        $products = [];
        $collection = Bootstrap::getObjectManager()->create(
            Collection::class,
            [
                'searchRequestName' => 'quick_search_container'
            ]
        );
        $collection->addSearchFilter($text);
        foreach ($collection as $product) {
            $products[] = $product;
        }

        return $products;
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (null !== $this->storeCodeBefore) {
            $this->storeManager->setCurrentStore($this->storeCodeBefore);
        }
    }
}
