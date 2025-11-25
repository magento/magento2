<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for getting cart items sorted
 */
#[
    DataFixture(ProductFixture::class, [
        'sku' => 'aaaaaa'
    ], as: 'p1'),
    DataFixture(ProductFixture::class, [
        'sku' => 'hhhhhh'
    ], as: 'p2'),
    DataFixture(ProductFixture::class, [
        'sku' => 'ddddddd'
    ], as: 'p3'),
    DataFixture(ProductFixture::class, [
        'sku' => 'wwwwww'
    ], as: 'p4'),
    DataFixture(ProductFixture::class, [
        'sku' => 'rrrrrr'
    ], as: 'p5'),
    DataFixture(GuestCartFixture::class, as: 'cart'),
    DataFixture(Indexer::class, as: 'indexer'),
    DataFixture(
        AddProductToCartFixture::class,
        [
            'cart_id' => '$cart.id$',
            'product_id' => '$p1.id$',
            'qty' => 1
        ],
        as: 'cart_item1'
    ),
    DataFixture(
        AddProductToCartFixture::class,
        [
            'cart_id' => '$cart.id$',
            'product_id' => '$p2.id$',
            'qty' => 5
        ],
        as: 'cart_item2'
    ),
    DataFixture(
        AddProductToCartFixture::class,
        [
            'cart_id' => '$cart.id$',
            'product_id' => '$p3.id$',
            'qty' => 2
        ],
        as: 'cart_item3'
    ),
    DataFixture(
        AddProductToCartFixture::class,
        [
            'cart_id' => '$cart.id$',
            'product_id' => '$p4.id$',
            'qty' => 8
        ],
        as: 'cart_item4'
    ),
    DataFixture(
        AddProductToCartFixture::class,
        [
            'cart_id' => '$cart.id$',
            'product_id' => '$p5.id$',
            'qty' => 3
        ],
        as: 'cart_item5'
    )
]
class GetCartSortedItemsTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorageManager
     */
    private $fixtures;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    public function testGetCartSortedAscBySortInputCriteria()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $query = $this->getQuery($maskedQuoteId, "QTY", "ASC");
        $response = $this->graphQlQuery($query);
        $expected = [
            'cart' => [
                'id' => $maskedQuoteId,
                'itemsV2' => [
                    'total_count' => 5,
                    'items' => [
                        [
                            'quantity' => 1,
                            'product' => [
                                'sku' => 'aaaaaa'
                            ]
                        ],
                        [
                            'quantity' => 2,
                            'product' => [
                                'sku' => 'ddddddd'
                            ]
                        ],
                        [
                            'quantity' => 3,
                            'product' => [
                                'sku' => 'rrrrrr'
                            ]
                        ],
                        [
                            'quantity' => 5,
                            'product' => [
                                'sku' => 'hhhhhh'
                            ]
                        ],
                        [
                            'quantity' => 8,
                            'product' => [
                                'sku' => 'wwwwww'
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $this->assertEquals(
            $expected,
            $response,
            sprintf("Expected:\n%s\ngot:\n%s", json_encode($expected), json_encode($response))
        );
    }

    public function testGetCartSortedDescBySortInputCriteria()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $query = $this->getQuery($maskedQuoteId, "SKU", "DESC");
        $response = $this->graphQlQuery($query);
        $expected = [
            'cart' => [
                'id' => $maskedQuoteId,
                'itemsV2' => [
                    'total_count' => 5,
                    'items' => [
                        [
                            'quantity' => 8,
                            'product' => [
                                'sku' => 'wwwwww'
                            ]
                        ],
                        [
                            'quantity' => 3,
                            'product' => [
                                'sku' => 'rrrrrr'
                            ]
                        ],
                        [
                            'quantity' => 5,
                            'product' => [
                                'sku' => 'hhhhhh'
                            ]
                        ],
                        [
                            'quantity' => 2,
                            'product' => [
                                'sku' => 'ddddddd'
                            ]
                        ],
                        [
                            'quantity' => 1,
                            'product' => [
                                'sku' => 'aaaaaa'
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $this->assertEquals(
            $expected,
            $response,
            sprintf("Expected:\n%s\ngot:\n%s", json_encode($expected), json_encode($response))
        );
    }

    public function testNonExistedOrderByFieldName()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Value "nonexistent" does not exist in "SortQuoteItemsEnum" enum.'
        );
        $this->graphQlQuery(<<<QUERY
        {
          cart(cart_id: "{$maskedQuoteId}") {
            id
            itemsV2(
                pageSize: 10,
                currentPage: 1,
                sort: {
                    field: nonexistent
                    order: ASC
                }
            ){
              total_count
              items {
                quantity
                product {
                    sku
                }
              }
            }
          }
        }
QUERY);
    }

    public function testNonExistedOrderName()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: Value "XYZ" does not exist in "SortEnum" enum'
        );
        $this->graphQlQuery(<<<QUERY
        {
          cart(cart_id: "{$maskedQuoteId}") {
            id
            itemsV2(
                pageSize: 10,
                currentPage: 1,
                sort: {
                    field: QTY
                    order: XYZ
                }
            ){
              total_count
              items {
                quantity
                product {
                    sku
                }
              }
            }
          }
        }
QUERY);
    }

    public function testInvalidSortInput()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Field QuoteItemsSortInput.field of required type SortQuoteItemsEnum! was not provided.'
        );
        $this->graphQlQuery(<<<QUERY
        {
          cart(cart_id: "{$maskedQuoteId}") {
            id
            itemsV2(
                pageSize: 10,
                currentPage: 1,
                sort: {}
            ){
              total_count
              items {
                quantity
                product {
                    sku
                }
              }
            }
          }
        }
QUERY);
    }

    public function testMissingSortOrderInput()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Field QuoteItemsSortInput.order of required type SortEnum! was not provided.'
        );
        $this->graphQlQuery(<<<QUERY
        {
          cart(cart_id: "{$maskedQuoteId}") {
            id
            itemsV2(
                pageSize: 10,
                currentPage: 1,
                sort: {
                    field: SKU
                }
            ){
              total_count
              items {
                quantity
                product {
                    sku
                }
              }
            }
          }
        }
QUERY);
    }

    public function testMissingSortFieldInput()
    {
        $cart = $this->fixtures->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Field QuoteItemsSortInput.field of required type SortQuoteItemsEnum! was not provided.'
        );
        $this->graphQlQuery(<<<QUERY
        {
          cart(cart_id: "{$maskedQuoteId}") {
            id
            itemsV2(
                pageSize: 10,
                currentPage: 1,
                sort: {
                    order: ASC
                }
            ){
              total_count
              items {
                quantity
                product {
                    sku
                }
              }
            }
          }
        }
QUERY);
    }

    /**
     * @param string $maskedQuoteId
     * @param string $orderBy
     * @param string $order
     * @return string
     */
    private function getQuery(string $maskedQuoteId, string $orderBy, string $order): string
    {
        return <<<QUERY
{
  cart(cart_id: "{$maskedQuoteId}") {
    id
    itemsV2(
        pageSize: 10,
        currentPage: 1,
        sort: {
            field: {$orderBy}
            order: {$order}
        }
    ){
      total_count
      items {
        quantity
        product {
            sku
        }
      }
    }
  }
}
QUERY;
    }
}
