<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\CatalogSearch\Model\Search;

use Magento\CatalogSearch\Model\Search\AttributeSearchWeightTest as CatalogSearchAttributeSearchWeightTest;
use Magento\TestModuleCatalogSearch\Model\ElasticsearchVersionChecker;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test founded products order after quick search with changed attribute search weight
 *
 * @magentoAppIsolation enabled
 */
class AttributeSearchWeightTest extends CatalogSearchAttributeSearchWeightTest
{
    /**
     * Perform search by word and check founded product order in different cases.
     *
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_sku_search_weight_score.php
     * @magentoDataFixture Magento/CatalogSearch/_files/full_reindex.php
     * @dataProvider attributeSearchWeightDataProvider
     * @magentoDbIsolation enabled
     *
     * @param string $searchQuery
     * @param array $attributeWeights
     * @param array $expectedProductNames
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     *
     * @return void
     */
    public function testAttributeSearchWeight(
        string $searchQuery,
        array $attributeWeights,
        array $expectedProductNames
    ): void {
        $this->markTestSkipped('This test need stabilization. MC-29260');
        parent::testAttributeSearchWeight($searchQuery, $attributeWeights, $expectedProductNames);
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
                    '1234-1234-1234-1234',
                    'Simple',
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
                    'name' => 10,
                    'test_searchable_attribute' => 9,
                    'sku' => 2,
                    'description' => 1,
                ],
                [
                    'Simple',
                    'Product with attribute',
                    '1234-1234-1234-1234',
                    'Product with description',
                ],
            ],
            'search_by_attribute_option' => [
                'Simple',
                [
                    'name' => 10,
                    'description' => 9,
                    'test_searchable_attribute' => 7,
                    'sku' => 2,
                ],
                [
                    'Simple',
                    'Product with description',
                    'Product with attribute',
                    '1234-1234-1234-1234',
                ],
            ],
        ];
    }
}
