<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\CatalogSearch\Model\Search;

use Magento\CatalogSearch\Model\Search\AttributeSearchWeightTest as CatalogSearchAttributeSearchWeightTest;

/**
 * Test founded products order after quick search with changed attribute search weight
 * using Elasticsearch 6.0+ search engine.
 *
 * @magentoAppIsolation enabled
 */
class AttributeSearchWeightTest extends CatalogSearchAttributeSearchWeightTest
{
    /**
     * Perform search by word and check founded product order in different cases.
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @magentoDataFixture Magento/CatalogSearch/_files/products_for_sku_search_weight_score.php
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
        $this->markTestSkipped('Skipped in connection with bug MC-29017');
        parent::testAttributeSearchWeight($searchQuery, $attributeWeights, $expectedProductNames);
    }
}
