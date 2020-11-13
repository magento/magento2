<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Product;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductSearchAggregationsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_boolean_attribute.php
     */
    public function testAggregationBooleanAttribute()
    {
        self::markTestSkipped('MC-22184: Elasticsearch returns incorrect aggregation options for booleans');

        $query = $this->getGraphQlQuery(
            '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"'
        );

        $result = $this->graphQlQuery($query);

        self::assertArrayNotHasKey('errors', $result);
        self::assertArrayHasKey('items', $result['products']);
        self::assertCount(5, $result['products']['items']);
        self::assertArrayHasKey('aggregations', $result['products']);

        $booleanAggregation = array_filter(
            $result['products']['aggregations'],
            static function ($a) {
                return $a['attribute_code'] == 'boolean_attribute';
            }
        );
        self::assertNotEmpty($booleanAggregation);
        $booleanAggregation = reset($booleanAggregation);
        self::assertEquals('Boolean Attribute', $booleanAggregation['label']);
        self::assertEquals('boolean_attribute', $booleanAggregation['attribute_code']);
        self::assertContainsEquals(['label' => '1', 'value'=> '1', 'count' => '3'], $booleanAggregation['options']);

        self::assertEquals(2, $booleanAggregation['count']);
        self::assertCount(2, $booleanAggregation['options']);
        self::assertContainsEquals(['label' => '0', 'value'=> '0', 'count' => '2'], $booleanAggregation['options']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_search.php
     */
    public function testAggregationPriceRanges()
    {
        $query = $this->getGraphQlQuery(
            '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"'
        );
        $result = $this->graphQlQuery($query);

        self::assertArrayNotHasKey('errors', $result);
        self::assertArrayHasKey('aggregations', $result['products']);

        $priceAggregation = array_filter(
            $result['products']['aggregations'],
            static function ($a) {
                return $a['attribute_code'] == 'price';
            }
        );
        self::assertNotEmpty($priceAggregation);
        $priceAggregation = reset($priceAggregation);
        self::assertEquals('Price', $priceAggregation['label']);
        self::assertEquals(4, $priceAggregation['count']);
        $expectedOptions = [
            ['label' => '10-20', 'value'=> '10_20', 'count' => '2'],
            ['label' => '20-30', 'value'=> '20_30', 'count' => '1'],
            ['label' => '30-40', 'value'=> '30_40', 'count' => '1'],
            ['label' => '40-50', 'value'=> '40_50', 'count' => '1']
        ];
        self::assertEquals($expectedOptions, $priceAggregation['options']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_search.php
     * @magentoApiDataFixture Magento/Directory/_files/usd_cny_rate.php
     * @magentoConfigFixture default_store currency/options/allow CNY,USD
     */
    public function testAggregationPriceRangesWithCurrencyHeader()
    {
        $headerMap['Content-Currency'] = 'CNY';
        $query = $this->getGraphQlQuery(
            '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"'
        );
        $result = $this->graphQlQuery($query, [], '', $headerMap);
        self::assertArrayNotHasKey('errors', $result);
        self::assertArrayHasKey('aggregations', $result['products']);
        $priceAggregation = array_filter(
            $result['products']['aggregations'],
            static function ($a) {
                return $a['attribute_code'] == 'price';
            }
        );
        self::assertNotEmpty($priceAggregation);
        $priceAggregation = reset($priceAggregation);
        self::assertEquals('Price', $priceAggregation['label']);
        self::assertEquals(4, $priceAggregation['count']);
        $expectedOptions = [
            ['label' => '70-140', 'value'=> '70_140', 'count' => '2'],
            ['label' => '140-210', 'value'=> '140_210', 'count' => '1'],
            ['label' => '210-280', 'value'=> '210_280', 'count' => '1'],
            ['label' => '280-350', 'value'=> '280_350', 'count' => '1']
        ];
        self::assertEquals($expectedOptions, $priceAggregation['options']);
    }

    private function getGraphQlQuery(string $skus)
    {
        return <<<QUERY
{
    products(filter: {sku: {in: [{$skus}]}}){
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
    }
}
