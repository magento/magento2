<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Layer\Search as CatalogLayerSearch;
use Magento\Catalog\Model\Product;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\Request\Config as RequestConfig;
use Magento\Search\Model\Search;
use Magento\TestFramework\Helper\Bootstrap;
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
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var array
     */
    private $collectedAttributesWeight = [];

    /**
     * @var CatalogLayerSearch
     */
    private $catalogLayerSearch;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->catalogLayerSearch = $this->objectManager->get(CatalogLayerSearch::class);
        $this->collectCurrentProductAttributesWeights();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->updateAttributesWeight($this->collectedAttributesWeight);
    }

    /**
     * Perform search by word and check founded product order in different cases.
     *
     * @magentoConfigFixture default/catalog/search/engine mysql
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_sku_search_weight_score.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
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
        $this->updateAttributesWeight($attributeWeights);
        $this->removeInstancesCache();
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
                '1234-1234-1234-1234',
                [
                    'sku' => 6,
                    'name' => 5,
                ],
                [
                    'Simple',
                    '1234-1234-1234-1234',
                ],
            ],
            'name_order_more_than_sku' => [
                '1234-1234-1234-1234',
                [
                    'name' => 6,
                    'sku' => 5,
                ],
                [
                    '1234-1234-1234-1234',
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
                    '1234-1234-1234-1234',
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
                    '1234-1234-1234-1234',
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
    protected function updateAttributesWeight(array $attributeWeights): void
    {
        foreach ($attributeWeights as $attributeCode => $weight) {
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
    protected function collectProductsName(array $products): array
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
    protected function removeInstancesCache(): void
    {
        $this->objectManager->removeSharedInstance(RequestConfig::class);
        $this->objectManager->removeSharedInstance(Builder::class);
        $this->objectManager->removeSharedInstance(Search::class);
        $this->objectManager->removeSharedInstance(CatalogLayerSearch::class);
    }

    /**
     * Find products by search query.
     *
     * @param string $query
     * @return Product[]
     */
    protected function findProducts(string $query): array
    {
        $testProductCollection = $this->catalogLayerSearch->getProductCollection();
        $testProductCollection->addSearchFilter($query);
        $testProductCollection->setOrder('relevance', 'desc');

        return $testProductCollection->getItems();
    }

    /**
     * Collect weight of attributes which use in test.
     *
     * @return void
     */
    private function collectCurrentProductAttributesWeights(): void
    {
        if (empty($this->collectedAttributesWeight)) {
            $attributeCodes = [
                'sku',
                'name',
                'description',
                'test_searchable_attribute'
            ];
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->productAttributeRepository->get($attributeCode);
                $this->collectedAttributesWeight[$attribute->getAttributeCode()] = $attribute->getSearchWeight();
            }
        }
    }
}
