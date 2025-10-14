<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query;

use Magento\Framework\GraphQl\Query\Fields;
use Magento\Framework\GraphQl\Query\QueryParser;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class FieldsTest extends TestCase
{
    /**
     * @var QueryParser
     */
    private $queryParser;

    /**
     * @var Fields
     */
    private $fields;

    protected function setUp(): void
    {
        $this->queryParser = new QueryParser();

        $this->fields = new Fields(
            $this->queryParser
        );
    }

    /**
     * @return array[]
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function setQueryDataProvider()
    {
        return [
            'mutation without variables' => [
                'query' => ['query' => 'mutation {
                  addProductsToCart(
                    cartId: "123"
                    cartItems: [
                      {
                        quantity: 1
                        sku: "sku1"
                      }
                    ]
                  ) {
                    cart {
                      items {
                        product {
                          name
                          sku
                        }
                        quantity
                      }
                    }
                    user_errors {
                      code
                      message
                    }
                  }
                }
                '],
                'variables' => [],
                'expected' => [
                    'addProductsToCart' => 'addProductsToCart',
                    'cartId' => 'cartId',
                    'cartItems' => 'cartItems',
                    'quantity' => 'quantity',
                    'sku' => 'sku',
                    'cart' => 'cart',
                    'items' => 'items',
                    'product' => 'product',
                    'name' => 'name',
                    'user_errors' => 'user_errors',
                    'code' => 'code',
                    'message' => 'message'
                ]
            ],
            'mutation with variables' => [
                'query' => ['query' => 'mutation ($cartId: String!, $products: [CartItemInput!]!) {
                addProductsToCart(cartId: $cartId, cartItems: $products) {
                    cart {
                      id
                      items {
                        uid
                        quantity
                        product {
                          sku
                          name
                          thumbnail {
                            url
                            __typename
                          }
                          __typename
                        }
                        prices {
                          price {
                            value
                            currency
                          }
                        }
                      }
                    }
                    user_errors {
                      code
                      message
                    }
                  }
                }'],
                'variables' => [
                    'cartId' => '123',
                    'products' => [
                        [
                            'sku' => 'sku1',
                            'parent_sku' => 'sku2',
                            'quantity' => 1
                        ]
                    ]
                ],
                'expected' => [
                    'cartId' => 'cartId',
                    'String' => 'String',
                    'products' => 'products',
                    'CartItemInput' => 'CartItemInput',
                    'addProductsToCart' => 'addProductsToCart',
                    'cartItems' => 'cartItems',
                    'cart' => 'cart',
                    'id' => 'id',
                    'items' => 'items',
                    'uid' => 'uid',
                    'quantity' => 'quantity',
                    'product' => 'product',
                    'sku' => 'sku',
                    'name' => 'name',
                    'thumbnail' => 'thumbnail',
                    'url' => 'url',
                    '__typename' => '__typename',
                    'prices' => 'prices',
                    'price' => 'price',
                    'value' => 'value',
                    'currency' => 'currency',
                    'user_errors' => 'user_errors',
                    'code' => 'code',
                    'message' => 'message',
                    'parent_sku' => 'parent_sku'
                ]
            ]
        ];
    }

    /**
     * @covers \Magento\Framework\GraphQl\Query\Fields::setQuery
     * @return void
     * @dataProvider setQueryDataProvider
     */
    public function testSetQuery(array $query, array $variables, $expected)
    {
        $this->fields->setQuery($query['query'], $variables);
        $result = $this->fields->getFieldsUsedInQuery();
        $this->assertArrayNotHasKey('0', $result);
        $this->assertEquals($expected, $result);
    }
}
