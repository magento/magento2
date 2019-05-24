<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch6\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config as RequestConfig;
use Magento\Framework\Search\Response\QueryResponse;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Indexer\Model\Indexer;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test for name over sku search weight of product attributes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttributeSearchWeightTest extends TestCase
{

    /** @var $objectManager ObjectManager */
    private $objectManager;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var ElasticsearchClient
     */
    private $client;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->connectionManager = $this->objectManager->create(ConnectionManager::class);
        $this->client = $this->connectionManager->getConnection();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * @param string $attributeName
     * @param int $searchWeight
     * @throws NoSuchEntityException
     * @throws StateException
     */
    private function setAttributeSearchWeight(string $attributeName, int $searchWeight)
    {
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->objectManager->create(AttributeRepositoryInterface::class);

        /** @var Attribute $attribute */
        $attribute = $attributeRepository->get('catalog_product', $attributeName);

        if ($attribute) {
            $attribute->setSearchWeight($searchWeight);
            $attributeRepository->save($attribute);
        }
    }

    /**
     * @throws \Throwable
     */
    private function reindex()
    {
        CacheCleaner::cleanAll();

        /** @var Indexer $indexer */
        $indexer = $this->objectManager->create(Indexer::class);
        $indexer->load('catalogsearch_fulltext');
        $indexer->reindexAll();
    }

    /**
     * @param string $query
     * @return array
     * @throws NoSuchEntityException
     */
    private function findProducts(string $query): array
    {
        $config = $this->objectManager->create(RequestConfig::class);

        /** @var Builder $requestBuilder */
        $requestBuilder = $this->objectManager->create(
            Builder::class,
            ['config' => $config]
        );
        $requestBuilder->bind('search_term', $query);
        $requestBuilder->setRequestName('quick_search_container');

        /** @var QueryResponse $searchResult */
        $searchResults = $this->objectManager->create(SearchEngineInterface::class)
            ->search($requestBuilder->create());

        $products = [];
        foreach ($searchResults as $searchResult) {
            $products [] = $this->productRepository->getById($searchResult->getId());
        }

        return $products;
    }

    /**
     * @dataProvider skuOverNameAttributeSearchWeightDataProvider
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoConfigFixture current_store catalog/search/elasticsearch_index_prefix composite_product_search
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_sku_search_weight_score.php
     * @param string $searchQuery
     * @param int $skuSearchWeight
     * @param int $nameSearchWeight
     * @param string $firstMatchProductName
     * @param string $secondMatchProductName
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    public function testSkuOverNameAttributeSearchWeight(
        string $searchQuery,
        int $skuSearchWeight,
        int $nameSearchWeight,
        string $firstMatchProductName,
        string $secondMatchProductName
    ) {
        $this->setAttributeSearchWeight('sku', $skuSearchWeight);
        $this->setAttributeSearchWeight('name', $nameSearchWeight);
        $this->reindex();

        /** @var Product $products [] */
        $products = $this->findProducts($searchQuery);

        $this->assertCount(
            2,
            $products,
            'Expected to find 2 products, found ' . count($products) . '.'
        );

        $this->assertEquals(
            $firstMatchProductName,
            $products[0]->getData('name'),
            'Products order is not as expected.'
        );
        $this->assertEquals(
            $secondMatchProductName,
            $products[1]->getData('name'),
            'Products order is not as expected.'
        );
    }

    public function skuOverNameAttributeSearchWeightDataProvider(): array
    {
        return [
            ['1-2-3-4', 10, 5, 'test', '1-2-3-4'],
            ['1-2-3-4', 5, 10, '1-2-3-4', 'test'],
        ];
    }
}
