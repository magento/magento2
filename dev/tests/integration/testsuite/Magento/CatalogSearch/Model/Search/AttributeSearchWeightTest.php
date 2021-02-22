<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\TestFramework\Catalog\Model\Layer\QuickSearchByQuery;
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
     * @var QuickSearchByQuery
     */
    private $quickSearchByQuery;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->quickSearchByQuery = $this->objectManager->get(QuickSearchByQuery::class);
        $this->collectCurrentProductAttributesWeights();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
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
        $actualProductNames = $this->quickSearchByQuery->execute($searchQuery)->getColumnValues('name');
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
            $attribute->setSearchWeight($weight);
            $this->productAttributeRepository->save($attribute);
        }
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
