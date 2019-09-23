<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductPriceTest extends GraphQlAbstract
{

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products.php
     */
    public function testProductWithSinglePrice()
    {
        $skus = ['simple'];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);

        $this->assertArrayNotHasKey('errors', $result);
        $this->assertNotEmpty($result['products']['items']);
        $product = $result['products']['items'][0];
        $this->assertNotEmpty($product['price_range']);

        $expectedPriceRange = [
            "minimum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 10
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ],
            "maximum_price" => [
                "regular_price" => [
                    "value" => 10
                ],
                "final_price" => [
                    "value" => 10
                ],
                "discount" => [
                    "amount_off" => 0,
                    "percent_off" => 0
                ]
            ]
        ];

        $this->assertPrices($expectedPriceRange, $product['price_range']);
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable_12345.php
     * @magentoApiDataFixture Magento/GroupedProduct/_files/product_grouped_with_simple.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     */
    public function testMultipleProductTypes()
    {
        $skus = ["simple-1", "12345", "grouped"];

        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(3, $result['products']['items']);

        $expected = [
            "simple-1" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 10
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 10
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ],
            "12345" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 30
                    ],
                    "final_price" => [
                        "value" => 30
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 40
                    ],
                    "final_price" => [
                        "value" => 40
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ],
            "grouped" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 100
                    ],
                    "final_price" => [
                        "value" => 100
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 100
                    ],
                    "final_price" => [
                        "value" => 100
                    ],
                    "discount" => [
                        "amount_off" => 0,
                        "percent_off" => 0
                    ]
                ]
            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertPrices($expected[$product['sku']], $product['price_range']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testProductWithSpecialPrice()
    {
        $skus = ["simple1", "simple2"];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(2, $result['products']['items']);

        $expected = [
            "simple1" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 5.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 40.1
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 5.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 40.1
                    ]
                ]
            ],
            "simple2" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 15.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 20.05
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 15.99
                    ],
                    "discount" => [
                        "amount_off" => 4.01,
                        "percent_off" => 20.05
                    ]
                ]
            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertPrices($expected[$product['sku']], $product['price_range']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoApiDataFixture Magento/CatalogRule/_files/catalog_rule_10_off_not_logged.php
     */
    public function testProductWithCatalogDiscount()
    {
        $skus = ["virtual-product", "configurable"];
        $query = $this->getProductQuery($skus);

        $result = $this->graphQlQuery($query);
        $this->assertArrayNotHasKey('errors', $result);
        $this->assertCount(2, $result['products']['items']);

        $expected = [
            "virtual-product" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ]
            ],
            "configurable" => [
                "minimum_price" => [
                    "regular_price" => [
                        "value" => 10
                    ],
                    "final_price" => [
                        "value" => 9
                    ],
                    "discount" => [
                        "amount_off" => 1,
                        "percent_off" => 10
                    ]
                ],
                "maximum_price" => [
                    "regular_price" => [
                        "value" => 20
                    ],
                    "final_price" => [
                        "value" => 18
                    ],
                    "discount" => [
                        "amount_off" => 2,
                        "percent_off" => 10
                    ]
                ]
            ]
        ];

        foreach ($result['products']['items'] as $product) {
            $this->assertNotEmpty($product['price_range']);
            $this->assertPrices($expected[$product['sku']], $product['price_range']);
        }
    }

    /**
     * Get GraphQl query to fetch products by sku
     *
     * @param array $skus
     * @return string
     */
    private function getProductQuery(array $skus): string
    {
        $stringSkus = '"' . implode('","', $skus) . '"';
        return <<<QUERY
{
  products(filter: {sku: {in: [$stringSkus]}}, sort: {name: ASC}) {
    items {
      name
      sku
      price_range {
        minimum_price {
          regular_price {
            value
            currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
        }
        maximum_price {
          regular_price {
            value
            currency
          }
          final_price {
            value
            currency
          }
          discount {
            amount_off
            percent_off
          }
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Check prices from graphql response
     *
     * @param $expectedPrices
     * @param $actualPrices
     * @param string $currency
     */
    private function assertPrices($expectedPrices, $actualPrices, $currency = 'USD')
    {
        $priceTypes = ['minimum_price', 'maximum_price'];

        foreach ($priceTypes as $priceType) {
            $expected = $expectedPrices[$priceType];
            $actual = $actualPrices[$priceType];
            $this->assertEquals($expected['regular_price']['value'], $actual['regular_price']['value']);
            $this->assertEquals(
                $expected['regular_price']['currency'] ?? $currency,
                $actual['regular_price']['currency']
            );
            $this->assertEquals($expected['final_price']['value'], $actual['final_price']['value']);
            $this->assertEquals(
                $expected['final_price']['currency'] ?? $currency,
                $actual['final_price']['currency']
            );
            $this->assertEquals($expected['discount']['amount_off'], $actual['discount']['amount_off']);
            $this->assertEquals($expected['discount']['percent_off'], $actual['discount']['percent_off']);
        }
    }
}
