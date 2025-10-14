<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\ConfigurableProduct;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test configurable product queries work correctly
 */
class MultipleConfigurableProductWithSameSimpleProductTest extends GraphQlAbstract
{
    /**
     * Test if multiple configurable product can have same simple product
     *
     */
    #[
        DataFixture(CategoryFixture::class, ['name' => 'Test category'], 'test_category'),
        DataFixture(Attribute::class, ['options' => [['label' => 'color', 'sort_order' => 0]]], as:'attribute'),
        DataFixture(
            ProductFixture::class,
            [
                'name' => 'Simple Product in Test Category',
                'sku' => 'simple-product-1',
                'category_ids' => ['$test_category.id$'],
                'price' => 10,
            ],
            'simple_product_1'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product 1',
                'sku' => 'configurable_product_1',
                'category_ids' => ['$test_category.id$'],
                '_options' => ['$attribute$'],
                '_links' => [
                    '$simple_product_1$'
                ]
            ],
            'configurable-product-1'
        ),
        DataFixture(
            ConfigurableProductFixture::class,
            [
                'name' => 'Configurable Product 2',
                'sku' => 'configurable_product_2',
                'category_ids' => ['$test_category.id$'],
                '_options' => ['$attribute$'],
                '_links' => [
                    '$simple_product_1$'
                ]
            ],
            'configurable-product-2'
        ),
    ]
    public function testMultipleConfigurableProductCanHaveSameSimpleProduct()
    {
        $query = $this->getGraphQlQuery('"configurable_product_1", "configurable_product_2"');
        $result = $this->graphQlQuery($query);

        self::assertArrayNotHasKey('errors', $result);
        self::assertNotEmpty($result['products']);
        self::assertCount(2, $result['products']['items']);
        self::assertNotEmpty($result['products']['items'][0]['variants'][0]);
        self::assertCount(2, $result['products']['items'][0]['variants'][0]);
        self::assertNotEmpty($result['products']['items'][1]['variants'][0]);
        self::assertCount(2, $result['products']['items'][1]['variants'][0]);
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
  products(filter: {sku: {in: [{$skus}]}}) {
    items {
      id
      attribute_set_id
      name
      sku
      __typename
      price {
        regularPrice {
          amount {
            currency
            value
          }
        }
      }
      categories {
        id
      }
      ... on ConfigurableProduct {
        configurable_options {
          id
          attribute_id_v2
          label
          position
          use_default
          attribute_code
          values {
            value_index
            label
          }
          product_id
        }
        variants {
          product {
            id
            name
            sku
            attribute_set_id
            ... on PhysicalProductInterface {
              weight
            }
            price_range{
              minimum_price{
                regular_price{
                  value
                  currency
                }
              }
            }
          }
          attributes {
            uid
            label
            code
            value_index
          }
        }
      }
    }
  }
}
QUERY;
    }
}
