<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductSearchAggregationsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_boolean_attribute.php
     */
    public function testAggregationBooleanAttribute()
    {
        $query = $this->getGraphQlQueryWithItems(
            '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"'
        );

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

        $this->assertEquals(2, $booleanAggregation['count']);
        $this->assertCount(2, $booleanAggregation['options']);
        $this->assertContainsEquals(['label' => '0', 'value'=> '0', 'count' => '2'], $booleanAggregation['options']);
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

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);

        $priceAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'price';
            }
        );
        $this->assertNotEmpty($priceAggregation);
        $priceAggregation = reset($priceAggregation);
        $this->assertEquals('Price', $priceAggregation['label']);
        $this->assertEquals(4, $priceAggregation['count']);
        $expectedOptions = [
            ['label' => '10-20', 'value'=> '10_20', 'count' => '2'],
            ['label' => '20-30', 'value'=> '20_30', 'count' => '1'],
            ['label' => '30-40', 'value'=> '30_40', 'count' => '1'],
            ['label' => '40-50', 'value'=> '40_50', 'count' => '1']
        ];
        $this->assertEquals($expectedOptions, $priceAggregation['options']);
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
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);
        $priceAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'price';
            }
        );
        $this->assertNotEmpty($priceAggregation);
        $priceAggregation = reset($priceAggregation);
        $this->assertEquals('Price', $priceAggregation['label']);
        $this->assertEquals(4, $priceAggregation['count']);
        $expectedOptions = [
            ['label' => '70-140', 'value'=> '70_140', 'count' => '2'],
            ['label' => '140-210', 'value'=> '140_210', 'count' => '1'],
            ['label' => '210-280', 'value'=> '210_280', 'count' => '1'],
            ['label' => '280-350', 'value'=> '280_350', 'count' => '1']
        ];
        $this->assertEquals($expectedOptions, $priceAggregation['options']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_for_search_with_custom_price_attribute.php
     */
    public function testAggregationCustomPriceAttribute()
    {
        $query = $this->getGraphQlQuery(
            '"search_product_1", "search_product_2", "search_product_3", "search_product_4" ,"search_product_5"'
        );
        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertArrayHasKey('aggregations', $result['products']);

        $priceAggregation = array_filter(
            $result['products']['aggregations'],
            function ($a) {
                return $a['attribute_code'] == 'product_price_attribute';
            }
        );
        $this->assertNotEmpty($priceAggregation);
        $priceAggregation = reset($priceAggregation);
        $this->assertEquals(2, $priceAggregation['count']);
        $expectedOptions = [
            ['label' => '0_1000', 'value'=> '0_1000', 'count' => '3'],
            ['label' => '1000_2000', 'value'=> '1000_2000', 'count' => '2']
        ];
        $this->assertEquals($expectedOptions, $priceAggregation['options']);
    }

    /**
     * Get GraphQl products query with aggregations
     *
     * @param string $skus
     * @return string
     */
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

    /**
     * Get GraphQl products query with aggregations and items
     *
     * @param string $skus
     * @return string
     */
    private function getGraphQlQueryWithItems(string $skus): string
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
    },
    items{
      id
    }
  }
}
QUERY;
    }
}
