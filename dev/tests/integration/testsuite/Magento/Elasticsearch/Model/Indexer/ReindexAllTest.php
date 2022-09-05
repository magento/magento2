<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\AdvancedSearch\Model\Client\ClientInterface as ElasticsearchClient;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\TestModuleCatalogSearch\Model\SearchEngineVersionReader;

/**
 * Important: Please make sure that each integration test file works with unique search index. In order to
 * achieve this, use @magentoConfigFixture to pass unique value for index_prefix for every test
 * method.
 * E.g. '@magentoConfigFixture current_store catalog/search/elasticsearch7_index_prefix indexerhandlertest_configurable'
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReindexAllTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var ElasticsearchClient
     */
    private $client;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $clientConfig;

    /**
     * @var SearchIndexNameResolver
     */
    private $searchIndexNameResolver;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->connectionManager = Bootstrap::getObjectManager()->create(ConnectionManager::class);
        $this->client = $this->connectionManager->getConnection();
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->clientConfig = Bootstrap::getObjectManager()->create(Config::class);
        $this->searchIndexNameResolver = Bootstrap::getObjectManager()->create(SearchIndexNameResolver::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Make sure that correct engine is set
     */
    protected function assertPreConditions(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $currentEngine = $objectManager->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $installedEngine = $objectManager->get(SearchEngineVersionReader::class)->getFullVersion();
        $this->assertEquals(
            $installedEngine,
            $currentEngine,
            sprintf(
                'Search engine configuration "%s" is not compatible with the installed version',
                $currentEngine
            )
        );
    }

    /**
     * Test search of all products after full reindex
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testSearchAll()
    {
        $this->reindexAll();
        $result = $this->searchByName('Configurable Product');
        self::assertGreaterThanOrEqual(2, $result);
    }

    /**
     * Test sorting of all products after full reindex
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testSort()
    {
        /** @var $productFifth \Magento\Catalog\Model\Product */
        $productSimple = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $productSimple->setTypeId('simple')
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('ABC')
            ->setSku('abc-first-in-sort')
            ->setPrice(20)
            ->setMetaTitle('meta title')
            ->setMetaKeyword('meta keyword')
            ->setMetaDescription('meta description')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 0])
            ->save();
        $productConfigurableOption = $this->productRepository->get('simple_10');
        $productConfigurableOption->setName('1ABC');
        $this->productRepository->save($productConfigurableOption);
        $this->reindexAll();
        $productSimple = $this->productRepository->get('abc-first-in-sort');
        $result = $this->sortByName();
        $firstInSearchResults = (int) $result[0]['_id'];
        $productSimpleId = (int) $productSimple->getId();
        $this->assertEquals($productSimpleId, $firstInSearchResults);
    }

    /**
     * Test sorting of products with lower and upper case names after full reindex
     *
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Elasticsearch/_files/case_sensitive.php
     */
    public function testSortCaseSensitive(): void
    {
        $productFirst = $this->productRepository->get('fulltext-1');
        $productSecond = $this->productRepository->get('fulltext-2');
        $productThird = $this->productRepository->get('fulltext-3');
        $productFourth = $this->productRepository->get('fulltext-4');
        $productFifth = $this->productRepository->get('fulltext-5');

        $this->reindexAll();
        $result = $this->sortByName();
        $firstInSearchResults = (int) $result[0]['_id'];
        $secondInSearchResults = (int) $result[1]['_id'];
        $thirdInSearchResults = (int) $result[2]['_id'];
        $fourthInSearchResults = (int) $result[3]['_id'];
        $fifthInSearchResults = (int) $result[4]['_id'];

        self::assertCount(5, $result);
        self::assertEqualsCanonicalizing(
            [$productFirst->getId(), $productFourth->getId()],
            [$firstInSearchResults, $secondInSearchResults]
        );
        self::assertEqualsCanonicalizing(
            [$productSecond->getId(), $productFifth->getId()],
            [$thirdInSearchResults, $fourthInSearchResults]
        );
        self::assertEquals($productThird->getId(), $fifthInSearchResults);
    }

    /**
     * Test search of specific product after full reindex
     *
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     * @dataProvider searchSpecificProductDataProvider
     * @param string $searchName
     * @param string $sku
     * @param int $expectedCount
     */
    public function testSearchSpecificProduct(string $searchName, string $sku, int $expectedCount)
    {
        $this->reindexAll();
        $result = $this->searchByName($searchName);
        self::assertCount($expectedCount, $result);

        $specificProduct = $this->productRepository->get($sku);
        self::assertEquals($specificProduct->getId(), $result[0]['_id']);
    }

    public function searchSpecificProductDataProvider(): array
    {
        return [
            'search by numeric name' => ['12345', 'configurable_12345', 1],
            'search by name with diacritics' => ['Cùstöm Dèsign', 'custom-design-simple-product', 1],
        ];
    }

    /**
     * @param string $text
     * @return array
     */
    private function searchByName($text)
    {
        $storeId = $this->storeManager->getDefaultStoreView()->getId();
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, 'catalogsearch_fulltext'),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'query' => [
                    'bool' => [
                        'minimum_should_match' => 1,
                        'should' => [
                            [
                                'match' => [
                                    'name' => $text,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->client->query($searchQuery);
        return isset($queryResult['hits']['hits']) ? $queryResult['hits']['hits'] : [];
    }

    /**
     * @return array
     */
    private function sortByName()
    {
        $storeId = $this->storeManager->getDefaultStoreView()->getId();
        $searchQuery = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, 'catalogsearch_fulltext'),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'sort' => [
                    'name.sort_name' => [
                        'order' => 'asc'
                    ],
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    'visibility' => [2, 4],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->client->query($searchQuery);
        return isset($queryResult['hits']['hits']) ? $queryResult['hits']['hits'] : [];
    }

    /**
     * Make fulltext catalog search reindex
     *
     * @return void
     */
    private function reindexAll()
    {
        // Perform full reindex
        /** @var Indexer $indexer */
        $indexer = Bootstrap::getObjectManager()->create(Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }
}
