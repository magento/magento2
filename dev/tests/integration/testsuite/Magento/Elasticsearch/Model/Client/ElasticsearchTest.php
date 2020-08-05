<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch6\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Elasticsearch/_files/configurable_products.php
 */
class ElasticsearchTest extends \PHPUnit\Framework\TestCase
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
        $objectManager = Bootstrap::getObjectManager();
        $this->connectionManager = $objectManager->create(ConnectionManager::class);
        $this->client = $this->connectionManager->getConnection();
        $this->storeManager = $objectManager->create(StoreManagerInterface::class);
        $this->clientConfig = $objectManager->create(Config::class);
        $this->searchIndexNameResolver = $objectManager->create(SearchIndexNameResolver::class);
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $indexer = $objectManager->create(Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
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
     * @param string $text
     * @return array
     */
    private function search($text)
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
                                    '_all' => $text,
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
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix composite_product_search
     */
    public function testSearchConfigurableProductBySimpleProductName()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('Configurable Option'));
    }

    /**
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix composite_product_search
     */
    public function testSearchConfigurableProductBySimpleProductAttributeMultiselect()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('dog'));
    }

    /**
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix composite_product_search
     */
    public function testSearchConfigurableProductBySimpleProductAttributeSelect()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('chair'));
    }

    /**
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix composite_product_search
     */
    public function testSearchConfigurableProductBySimpleProductAttributeShortDescription()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('simpledescription'));
    }

    /**
     * Assert that product with SKU is present in response
     *
     * @param string $sku
     * @param array $result
     * @return bool
     */
    private function assertProductWithSkuFound($sku, array $result)
    {
        foreach ($result as $item) {
            if ($item['_source']['sku'] == $sku) {
                return true;
            }
        }
        return false;
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
