<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Product filtering by condition "FROM..TO" for "Price" attribute
 *
 * Preconditions:
 *   Fixture simple products created
 * Steps:
 *   Send request:
 *     query test {
 *       products(search: "Product", filter: {price: {from: "0.01" to: "9.99"}}, sort: {price: ASC}) {
 *         items {
 *           name
 *         }
 *         total_count
 *       }
 *     }
 *   Expected Response:
 * {
 *   "data": {
 *     "products": {
 *       "items": [
 *         {
 *           "name": "Product 2 $0.01"
 *         },
 *         {
 *           "name": "Product 3 $5"
 *         },
 *         {
 *           "name": "Product 4 $9.99"
 *         }
 *       ],
 *       "total_count": 3
 *     }
 *   }
 * }
 */
class ProductSearchPriceFilterTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Verify that search returns correct values for given price filter
     *
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_different_price.php
     * @param string $priceFilter
     * @param string $sort
     * @param array $items
     * @return void
     * @dataProvider productSearchPriceDataProvider
     * @throws \Exception
     */
    public function testProductSearchPriceFilter($priceFilter, string $sort, array $items): void
    {
        // expected stuff
        $totalCount = count($items);
        $expectedFirstItemPriceValue = reset($items)['price']['minimalPrice']['amount']['value'];
        $expectedLastItemPriceValue = end($items)['price']['minimalPrice']['amount']['value'];
        $assertionMap = [
            'products' => [
                'items' => $items,
                'total_count' => $totalCount,
            ],
        ];

        $query = <<<QUERY
{
  products(search: "Product", filter: {price: {{$priceFilter}}}, sort: {{$sort}}) {
    items {
      name
      price {
        minimalPrice {
          amount {
            value
          }
        }
      }
    }
    total_count
  }
}
QUERY;

        $response = $this->graphQlQuery($query);

        // check are there any items in the return data
        self::assertNotNull(
            $response['products']['items'],
            'product items must not be null'
        );

        // check for the total of items in return
        self::assertCount(
            $totalCount,
            $response['products']['items'],
            "there are should be $totalCount products in price range $priceFilter"
        );

        // prepare first and last item from response for assertions
        $responseFirstItem = reset($response['products']['items']);
        $responseLastItem = end($response['products']['items']);
        // check are there price in the first item
        self::assertArrayHasKey('price', $responseFirstItem, 'product item must have price');
        // check are there price in for the last item
        self::assertArrayHasKey('price', $responseLastItem, 'product item must have price');

        // prepare first and last item price value from response for assertions
        $responseFirstItemPriceValue = $responseFirstItem['price']['minimalPrice']['amount']['value'] ?? null;
        $responseLastItemPriceValue = $responseLastItem['price']['minimalPrice']['amount']['value'] ?? null;
        // check are there price value in for the first item
        self::assertNotNull($responseFirstItemPriceValue, 'first product item must have price value');
        // check are there price value in for the first item
        self::assertNotNull($responseLastItemPriceValue, 'last product item must have price value');

        // check price value for the first item in return
        self::assertEquals(
            $expectedFirstItemPriceValue,
            $responseFirstItemPriceValue,
            sprintf(
                'price for the first product must be %s as it sorted by price ASC',
                $expectedFirstItemPriceValue
            )
        );

        // check price value for the last item in return
        self::assertEquals(
            $expectedLastItemPriceValue,
            $responseLastItemPriceValue,
            sprintf(
                'price for the first product must be %s as it sorted by price ASC',
                $expectedLastItemPriceValue
            )
        );

        // check entire response
        $this->assertResponseFields($response, $assertionMap);
    }

    /**
     * Data provider for product search price filter
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array[][]
     */
    public static function productSearchPriceDataProvider(): array
    {
        return [
            [
                'priceFilter' => 'from: "0.01" to: "9.99"',
                'sort' => 'price: ASC',
                'items' => [
                    [
                        'name' => 'Product with price 0.01',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 0.01,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Product with price 5',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 5,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Product with price 9.99',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 9.99,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'priceFilter' => 'from: "5.01" to: "10"',
                'sort' => 'price: DESC',
                'items' => [
                    [
                        'name' => 'Product with price 10',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 10,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Product with price 9.99',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 9.99,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'priceFilter' => 'from: "5"',
                'sort' => 'price: DESC',
                'items' => [
                    [
                        'name' => 'Product with price 10',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 10,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Product with price 9.99',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 9.99,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Product with price 5',
                        'price' => [
                            'minimalPrice' => [
                                'amount' => [
                                    'value' => 5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
