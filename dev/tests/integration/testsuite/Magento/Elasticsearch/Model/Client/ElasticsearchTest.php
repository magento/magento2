<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;
use Magento\TestModuleCatalogSearch\Model\SearchEngineVersionReader;
use Magento\Framework\Search\EngineResolverInterface;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @magentoDataFixture Magento/Elasticsearch/_files/configurable_products.php
 */
class ElasticsearchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var ClientInterface
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

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->connectionManager = $objectManager->create(ConnectionManager::class);
        $this->client = $this->connectionManager->getConnection();
        $this->storeManager = $objectManager->create(StoreManagerInterface::class);
        $this->clientConfig = $objectManager->create(Config::class);
        $this->searchIndexNameResolver = $objectManager->create(SearchIndexNameResolver::class);
        $indexer = $objectManager->create(Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
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
     * @return void
     */
    public function testSearchConfigurableProductBySimpleProductName()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('Configurable Option'));
    }

    /**
     * @return void
     */
    public function testSearchConfigurableProductBySimpleProductAttributeMultiselect()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('dog'));
    }

    /**
     * @return void
     */
    public function testSearchConfigurableProductBySimpleProductAttributeSelect()
    {
        $this->assertProductWithSkuFound('configurable', $this->search('chair'));
    }

    /**
     * @return void
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
}
