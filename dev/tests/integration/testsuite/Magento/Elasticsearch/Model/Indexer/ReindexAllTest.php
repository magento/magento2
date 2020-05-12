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
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;

/**
 * Important: Please make sure that each integration test file works with unique elastic search index. In order to
 * achieve this, use @magentoConfigFixture to pass unique value for 'elasticsearch_index_prefix' for every test
 * method.
 * E.g. '@magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest_configurable'
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReindexAllTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $searchEngine;

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
        $currentEngine = Bootstrap::getObjectManager()->get(EngineResolverInterface::class)->getCurrentSearchEngine();
        $this->assertEquals($this->getInstalledSearchEngine(), $currentEngine);
    }

    /**
     * Test search of all products after full reindex
     *
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest_configurable
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
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest_configurable
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
     * Test search of specific product after full reindex
     *
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest_configurable
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_products.php
     */
    public function testSearchSpecificProduct()
    {
        $this->reindexAll();
        $result = $this->searchByName('12345');
        self::assertCount(1, $result);

        $specificProduct = $this->productRepository->get('configurable_12345');
        self::assertEquals($specificProduct->getId(), $result[0]['_id']);
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

    /**
     * Returns installed on server search service
     *
     * @return string
     */
    private function getInstalledSearchEngine()
    {
        if (!$this->searchEngine) {
            // phpstan:ignore "Class Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker not found."
            $version = Bootstrap::getObjectManager()->get(ElasticsearchVersionChecker::class)->getVersion();
            $this->searchEngine = 'elasticsearch' . $version;
        }
        return $this->searchEngine;
    }
}
