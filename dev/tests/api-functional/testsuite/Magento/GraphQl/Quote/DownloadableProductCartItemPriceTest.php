<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Downloadable\Test\Fixture\DownloadableProduct as DownloadableProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test getting CartItemPrices schema with downloadable product
 */
class DownloadableProductCartItemPriceTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Test cart item price for a downloadable product without separate link selection
     *
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(DownloadableProductFixture::class, [
            'price' => 100,
            'type_id' => 'downloadable',
            'links_purchased_separately' => 0,
            'downloadable_product_links' => [
                [
                    'title' => 'Example 1',
                    'price' => 0.00,
                    'link_type' => 'url'
                ],
                [
                    'title' => 'Example 2',
                    'price' => 0.00,
                    'link_type' => 'url'
                ],
            ]
        ], as: 'product'),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1
        ]),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testGetCartItemPricesForDownloadableProductWithoutSeparateLinks()
    {
        $query = <<<QUERY
        {
          cart(cart_id: "{$this->fixtures->get('quoteIdMask')->getMaskedId()}") {
            items {
              ... on DownloadableCartItem {
                prices {
                  original_item_price {
                    value
                    currency
                  }
                  original_row_total {
                    value
                    currency
                  }
                }
                product {
                  price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                      final_price {
                        value
                      }
                    }
                  }
                }
              }
            }
          }
        }
        QUERY;
        self::assertEquals(
            [
                'cart' => [
                    'items' => [
                        0 => [
                            'prices' => [
                                'original_item_price' => [
                                    'value' => 100,
                                    'currency' => 'USD'
                                ],
                                'original_row_total' => [
                                    'value' => 100,
                                    'currency' => 'USD'
                                ]
                            ],
                            'product' => [
                                'price_range' => [
                                    'maximum_price' => [
                                        'regular_price' => [
                                            'value' => 100
                                        ],
                                        'final_price' => [
                                            'value' => 100
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders())
        );
    }

    /**
     * Test cart item price for a downloadable product with separate link selection
     *
     * @return void
     * @throws AuthenticationException
     * @throws LocalizedException
     * @throws Exception
     */
    #[
        DataFixture(DownloadableProductFixture::class, [
            'price' => 0,
            'type_id' => 'downloadable',
            'links_purchased_separately' => 1,
            'downloadable_product_links' => [
                [
                    'title' => 'Example 1',
                    'price' => 10,
                    'link_type' => 'url'
                ],
                [
                    'title' => 'Example 2',
                    'price' => 10,
                    'link_type' => 'url'
                ],
            ]
        ], as: 'product'),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask')
    ]
    public function testGetCartItemPricesForDownloadableProductWithSeparateLinks()
    {
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $linkId = key($product->getDownloadableLinks());

        $query = <<<MUTATION
        mutation {
            addDownloadableProductsToCart(
                input: {
                    cart_id: "{$this->fixtures->get('quoteIdMask')->getMaskedId()}",
                    cart_items: [
                        {
                            data: {
                                quantity: 1,
                                sku: "{$product->getSku()}"
                            },
                            downloadable_product_links: [
                                {
                                    link_id: {$linkId}
                                }
                            ]
                        }
                    ]
                }
            ) {
          cart {
            items {
              ... on DownloadableCartItem {
                prices {
                  original_item_price {
                    value
                    currency
                  }
                  original_row_total {
                    value
                    currency
                  }
                }
                product {
                  price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                      final_price {
                        value
                      }
                    }
                  }
                }
              }
            }
          }
         }
        }
        MUTATION;

        self::assertEquals(
            [
                'addDownloadableProductsToCart' => [
                    'cart' => [
                        'items' => [
                            0 => [
                                'prices' => [
                                    'original_item_price' => [
                                        'value' => 10,
                                        'currency' => 'USD'
                                    ],
                                    'original_row_total' => [
                                        'value' => 10,
                                        'currency' => 'USD'
                                    ]
                                ],
                                'product' => [
                                    'price_range' => [
                                        'maximum_price' => [
                                            'regular_price' => [
                                                'value' => 20
                                            ],
                                            'final_price' => [
                                                'value' => 20
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders())
        );
    }

    /**
     * Get Customer Auth Headers
     *
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(): array
    {
        $customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $customerToken = $customerTokenService->createCustomerAccessToken('customer@example.com', 'password');

        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
