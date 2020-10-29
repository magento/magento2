<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Config\App\Config\Type\System;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Directory\Model\Currency;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductSearchAggregationsTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_boolean_attribute.php
     */
    public function testAggregationBooleanAttribute()
    {
        $this->markTestSkipped(
            'MC-22184: Elasticsearch returns incorrect aggregation options for booleans'
            . 'MC-36768: Custom attribute not appears in elasticsearch'
        );

        $query = $this->getGraphQlQuery(
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

        $this->markTestSkipped('MC-22184: Elasticsearch returns incorrect aggregation options for booleans');
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
     * @magentoConfigFixture default_store currency/options/allow EUR,USD
     */
    public function testAggregationPriceRangesWithCurrencyHeader()
    {
        $headerMap['Content-Currency'] = 'EUR';
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
            ['label' => '20-40', 'value'=> '20_40', 'count' => '2'],
            ['label' => '40-60', 'value'=> '40_60', 'count' => '1'],
            ['label' => '60-80', 'value'=> '60_80', 'count' => '1'],
            ['label' => '80-100', 'value'=> '80_100', 'count' => '1']
        ];
        $this->assertEquals($expectedOptions, $priceAggregation['options']);
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
