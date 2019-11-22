<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
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
 * Test founded products order after quick search with changed attribute search weight using mysql search engine.
 *
 * @magentoAppIsolation enabled
 */
class AttributeSearchWeightTest extends TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->indexer = $this->objectManager->get(Indexer::class);
        $config = $this->objectManager->create(RequestConfig::class);
        $this->requestBuilder = $this->objectManager->create(
            Builder::class,
            [
                'config' => $config
            ]
        );
        $this->searchEngine = $this->objectManager->create(SearchEngineInterface::class);
    }

    /**
     * Perform search by word and check founded product order in different cases.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_sku_search_weight_score.php
     * @dataProvider attributeSearchWeightDataProvider
     * @magentoDbIsolation disabled
     *
     * @param string $searchQuery
     * @param array $attributeWeights
     * @param array $expectedProductNames
     * @return void
     */
    public function testAttributeSearchWeight(
        string $searchQuery,
        array $attributeWeights,
        array $expectedProductNames
    ): void {
        $this->markTestSkipped('Skipped in connection with bug MC-29076');
        $this->updateAttributesWeight($attributeWeights);
        $this->reindex();
        $products = $this->findProducts($searchQuery);
        $actualProductNames = $this->collectProductsName($products);
        $this->assertEquals($expectedProductNames, $actualProductNames, 'Products order is not as expected.');
    }

    /**
     * Data provider with word for quick search, attributes weight and expected products name order.
     *
     * @return array
     */
    public function attributeSearchWeightDataProvider(): array
    {
        return [
            'sku_order_more_than_name' => [
                '1-2-3-4',
                [
                    'sku' => 6,
                    'name' => 5,
                ],
                [
                    'Simple',
                    '1-2-3-4',
                ],
            ],
            'name_order_more_than_sku' => [
                '1-2-3-4',
                [
                    'name' => 6,
                    'sku' => 5,
                ],
                [
                    '1-2-3-4',
                    'Simple',
                ],
            ],
            'search_by_word_from_description' => [
                'Simple',
                [
                    'test_searchable_attribute' => 8,
                    'sku' => 6,
                    'name' => 5,
                    'description' => 1,
                ],
                [
                    'Product with attribute',
                    '1-2-3-4',
                    'Simple',
                    'Product with description',
                ],
            ],
            'search_by_attribute_option' => [
                'Simple',
                [
                    'description' => 10,
                    'test_searchable_attribute' => 8,
                    'sku' => 6,
                    'name' => 1,
                ],
                [
                    'Product with description',
                    'Product with attribute',
                    '1-2-3-4',
                    'Simple',
                ],
            ],
        ];
    }

    /**
     * Update attributes weight.
     *
     * @param array $attributeWeights
     * @return void
     */
    private function updateAttributesWeight(array $attributeWeights): void
    {
        foreach ($attributeWeights as $attributeCode => $weight) {
            /** @var Attribute $attribute */
            $attribute = $this->productAttributeRepository->get($attributeCode);

            if ($attribute) {
                $attribute->setSearchWeight($weight);
                $this->productAttributeRepository->save($attribute);
            }
        }
    }

    /**
     * Get all names from founded products.
     *
     * @param Product[] $products
     * @return array
     */
    private function collectProductsName(array $products): array
    {
        $result = [];
        foreach ($products as $product) {
            $result[] = $product->getName();
        }

        return $result;
    }

    /**
     * Reindex catalogsearch fulltext index.
     *
     * @return void
     */
    private function reindex(): void
    {
        CacheCleaner::cleanAll();
        $this->indexer->load(Fulltext::INDEXER_ID);
        $this->indexer->reindexAll();
    }

    /**
     * Find products by search query.
     *
     * @param string $query
     * @return Product[]
     */
    private function findProducts(string $query): array
    {
        $this->requestBuilder->bind('search_term', $query);
        $this->requestBuilder->setRequestName('quick_search_container');
        /** @var QueryResponse $searchResult */
        $searchResults = $this->searchEngine->search($this->requestBuilder->create());

        $products = [];
        foreach ($searchResults as $searchResult) {
            $products[] = $this->productRepository->getById($searchResult->getId());
        }

        return $products;
    }
}
