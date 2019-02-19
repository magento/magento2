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
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

/**
 * Important: Please make sure that each integration test file works with unique elastic search index. In order to
 * achieve this, use @magentoConfigFixture to pass unique value for 'elasticsearch_index_prefix' for every test
 * method.
 * E.g. '@magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix indexerhandlertest_configurable'
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
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

    protected function setUp()
    {
        $this->connectionManager = Bootstrap::getObjectManager()->create(ConnectionManager::class);
        $this->client = $this->connectionManager->getConnection();
        $this->storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $this->clientConfig = Bootstrap::getObjectManager()->create(Config::class);
        $this->searchIndexNameResolver = Bootstrap::getObjectManager()->create(SearchIndexNameResolver::class);
        $this->productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);
    }

    /**
     * Test search of all products after full reindex
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch
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
     * Test search of specific product after full reindex
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch
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
