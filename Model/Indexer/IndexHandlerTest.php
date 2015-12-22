<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\Search\Request\Dimension;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

/**
 * @magentoDbIsolation disabled
 * @magentoDataFixture Magento/Elasticsearch/_files/indexer.php
 */
class IndexHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var Dimension[]
     */
    protected $dimensions;

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
        $this->indexer = Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Indexer'
        );
        $this->indexer->load('catalogsearch_fulltext');

        $this->connectionManager = Bootstrap::getObjectManager()->create(
            'Magento\Elasticsearch\SearchAdapter\ConnectionManager'
        );

        $this->client = $this->connectionManager->getConnection();

        $mainStore = Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Search\Request\Dimension',
            ['name' => 'scope', 'value' => '1']
        );
        $secondaryStore = Bootstrap::getObjectManager()->create(
            '\Magento\Framework\Search\Request\Dimension',
            ['name' => 'scope', 'value' => '2']
        );
        $this->dimensions = [$mainStore, $secondaryStore];

        $this->clientConfig = Bootstrap::getObjectManager()->create(
            'Magento\Elasticsearch\Model\Config'
        );

        $this->searchIndexNameResolver = Bootstrap::getObjectManager()->create(
            'Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver'
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
        $this->indexer->reindexAll();

        foreach ($this->dimensions as $dimension) {
            $storeId = $dimension->getValue();

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
        $this->productApple->setData('name', 'Simple Product Cucumber');
        $this->productApple->save();

        foreach ($this->dimensions as $dimension) {
            $storeId = $dimension->getValue();

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
        $productIds = [
            $this->productApple->getId(),
            $this->productBanana->getId(),
        ];
        $attrData = [
            'name' => 'Simple Product Common',
        ];

        /** @var \Magento\Catalog\Model\Product\Action $action */
        $action = Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Model\Product\Action'
        );
        $action->updateAttributes($productIds, $attrData, 1);

        foreach ($this->dimensions as $dimension) {
            $storeId = $dimension->getValue();

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
        $this->indexer->reindexAll();
        $this->productBanana->delete();

        foreach ($this->dimensions as $dimension) {
            $storeId = $dimension->getValue();

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
            'Magento\Catalog\Model\Product'
        );
        return $product->loadByAttribute('sku', $sku);
    }
}
