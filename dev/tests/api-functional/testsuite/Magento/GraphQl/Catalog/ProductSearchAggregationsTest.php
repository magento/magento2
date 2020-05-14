<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductSearchAggregationsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_boolean_attribute.php
     */
    public function testAggregationBooleanAttribute()
    {
        $this->reindex();

        $skus= '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"';
        $query = <<<QUERY
{
    products(filter: {sku: {in: [{$skus}]}}){
    items{
      id
      sku
      name
    }
    aggregations{
      label
      attribute_code
      count
      options{
        label
        value
        count
      }
    }
  }
}
QUERY;

        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('items', $result['products']);
        $this->assertCount(5, $result['products']['items']);
        $this->assertArrayHasKey('aggregations', $result['products']);

        $booleanAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'boolean_attribute';
            }
        );
        $this->assertNotEmpty($booleanAggregation);
        $booleanAggregation = reset($booleanAggregation);
        $this->assertEquals('Boolean Attribute', $booleanAggregation['label']);
        $this->assertEquals('boolean_attribute', $booleanAggregation['attribute_code']);
        $this->assertContainsEquals(['label' => '1', 'value'=> '1', 'count' => '3'], $booleanAggregation['options']);

        $this->markTestIncomplete('MC-22184: Elasticsearch returns incorrect aggregation options for booleans');
        $this->assertEquals(2, $booleanAggregation['count']);
        $this->assertCount(2, $booleanAggregation['options']);
        $this->assertContainsEquals(['label' => '0', 'value'=> '0', 'count' => '2'], $booleanAggregation['options']);
    }

    /**
     * Reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function reindex()
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex");
    }
}
