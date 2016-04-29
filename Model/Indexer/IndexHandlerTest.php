<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

/**
 * @magentoDbIsolation enabled
 * magentoDataFixture Magento/Elasticsearch/_files/indexer.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var int[]
     */
    protected $storeIds;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var SearchIndexNameResolver
     */
    protected $searchIndexNameResolver;

    /**
     * @var Product
     */
    protected $productApple;

    /**
     * @var Product
     */
    protected $productBanana;

    /**
     * @var Product
     */
    protected $productOrange;

    /**
     * @var Product
     */
    protected $productPapaya;

    /**
     * @var Product
     */
    protected $productCherry;

    /**
     * Setup method
     */
    protected function setUp()
    {
        //remember to add @ on line 18 when MAGETWO-44489 is done
        $this->markTestSkipped('MAGETWO-44489 - Skipping until Elastic search support becomes available on Bamboo.');

        $this->connectionManager = Bootstrap::getObjectManager()->create(
            \Magento\Elasticsearch\SearchAdapter\ConnectionManager::class
        );

        $this->client = $this->connectionManager->getConnection();

        $this->storeManager = Bootstrap::getObjectManager()->create(
            \Magento\Store\Model\StoreManagerInterface::class
        );
        $this->storeIds = array_keys($this->storeManager->getStores());

        $this->clientConfig = Bootstrap::getObjectManager()->create(
            \Magento\Elasticsearch\Model\Config::class
        );

        $this->searchIndexNameResolver = Bootstrap::getObjectManager()->create(
            \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver::class
        );

        $this->productApple = $this->getProductBySku('fulltext-1');
        $this->productBanana = $this->getProductBySku('fulltext-2');
        $this->productOrange = $this->getProductBySku('fulltext-3');
        $this->productPapaya = $this->getProductBySku('fulltext-4');
        $this->productCherry = $this->getProductBySku('fulltext-5');
    }

    /**
     * Test reindex process
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testReindexAll()
    {
        $this->reindexAll();
        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(1, $products);
            $this->assertEquals($this->productApple->getId(), $products[0]['_id']);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testReindexRowAfterEdit()
    {
        $this->reindexAll();
        $this->productApple->setData('name', 'Simple Product Cucumber');
        $this->productApple->save();

        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Cucumber', $storeId);
            $this->assertCount(1, $products);
            $this->assertEquals($this->productApple->getId(), $products[0]['_id']);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     */
    public function testReindexRowAfterMassAction()
    {
        $this->reindexAll();
        $productIds = [
            $this->productApple->getId(),
            $this->productBanana->getId(),
        ];
        $attrData = [
            'name' => 'Simple Product Common',
        ];

        /** @var \Magento\Catalog\Model\Product\Action $action */
        $action = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Product\Action::class
        );

        foreach ($this->storeIds as $storeId) {
            $action->updateAttributes($productIds, $attrData, $storeId);

            $products = $this->searchByName('Apple', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Banana', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Unknown', $storeId);
            $this->assertCount(0, $products);

            $products = $this->searchByName('Common', $storeId);
            $this->assertCount(2, $products);

            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(5, $products);
        }
    }

    /**
     * @magentoConfigFixture current_store catalog/search/engine elasticsearch
     * @magentoAppArea adminhtml
     */
    public function testReindexRowAfterDelete()
    {
        $this->reindexAll();
        $this->productBanana->delete();

        foreach ($this->storeIds as $storeId) {
            $products = $this->searchByName('Simple Product', $storeId);
            $this->assertCount(4, $products);
        }
    }

    /**
     * Search docs in Elasticsearch by name
     *
     * @param string $text
     * @param int $storeId
     * @return array
     */
    protected function searchByName($text, $storeId)
    {
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
        $products = isset($queryResult['hits']['hits']) ? $queryResult['hits']['hits'] : [];
        return $products;
    }

    /**
     * Return product by SKU
     *
     * @param string $sku
     * @return Product
     */
    protected function getProductBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\Product::class
        );
        return $product->loadByAttribute('sku', $sku);
    }

    /**
     * Perform full reindex
     *
     * @return void
     */
    private function reindexAll()
    {
        $indexer = Bootstrap::getObjectManager()->create(
            \Magento\Indexer\Model\Indexer::class
        );
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }
}
