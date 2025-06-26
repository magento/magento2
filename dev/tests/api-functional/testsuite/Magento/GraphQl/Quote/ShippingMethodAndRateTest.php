<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ShippingMethodAndRateTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
    }

    #[
        Config('carriers/freeshipping/active', true),
        Config('carriers/flatrate/active', true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$'], 'email'),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testCheckoutAfterReturningToCartForFlatrate(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $this->graphQlMutation(
            $this->setShippingMethodMutation(
                $maskedQuoteId,
                'freeshipping',
                'freeshipping',
            )
        );
        $this->graphQlMutation(
            $this->getEstimateTotalsMutation(
                $maskedQuoteId,
                'flatrate',
                'flatrate'
            )
        );
        $this->assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        [
                            'selected_shipping_method' => [
                                'carrier_code' => 'flatrate',
                                'carrier_title' => 'Flat Rate',
                                'method_code' => 'flatrate',
                                'method_title' => 'Fixed',
                            ],
                        ],
                    ],
                ],
            ],
            $this->graphQlQuery($this->getCartQuery($maskedQuoteId))
        );
    }

    #[
        Config('carriers/freeshipping/active', true),
        Config('carriers/flatrate/active', true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$'], 'email'),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testCheckoutAfterReturningToCartForFreeshipping(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $this->graphQlMutation(
            $this->setShippingMethodMutation(
                $maskedQuoteId,
                'freeshipping',
                'freeshipping',
            )
        );
        $this->graphQlMutation(
            $this->getEstimateTotalsMutation(
                $maskedQuoteId,
                'freeshipping',
                'freeshipping'
            )
        );
        $this->assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        [
                            'selected_shipping_method' => [
                                'carrier_code' => 'freeshipping',
                                'carrier_title' => 'Free Shipping',
                                'method_code' => 'freeshipping',
                                'method_title' => 'Free',
                            ],
                        ],
                    ],
                ],
            ],
            $this->graphQlQuery($this->getCartQuery($maskedQuoteId))
        );
    }

    #[
        Config('carriers/freeshipping/active', true),
        Config('carriers/flatrate/active', true),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCart::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$'], 'email'),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testCheckoutAfterReturningToCartWithoutChangingShippingMethod(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $this->graphQlMutation(
            $this->setShippingMethodMutation(
                $maskedQuoteId,
                'freeshipping',
                'freeshipping',
            )
        );
        $this->graphQlMutation(
            $this->getEstimateTotalsMutation(
                $maskedQuoteId,
                null,
                null
            )
        );
        $this->assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        [
                            'selected_shipping_method' => [
                                'carrier_code' => 'freeshipping',
                                'carrier_title' => 'Free Shipping',
                                'method_code' => 'freeshipping',
                                'method_title' => 'Free',
                            ],
                        ],
                    ],
                ],
            ],
            $this->graphQlQuery($this->getCartQuery($maskedQuoteId))
        );
    }

    /**
     * Set Shipping method mutation
     *
     * @param string $cartId
     * @param string $methodCode
     * @param string $carrierCode
     * @return string
     */
    private function setShippingMethodMutation(string $cartId, string $methodCode, string $carrierCode): string
    {
        return <<<MUTATION
            mutation {
              setShippingMethodsOnCart(
                input: {
                  cart_id: "{$cartId}",
                  shipping_methods: [
                    {
                      carrier_code: "{$carrierCode}",
                      method_code: "{$methodCode}"
                    }
                  ]
                }
              ) {
                cart {
                  shipping_addresses {
                    selected_shipping_method {
                      carrier_code
                      method_code
                    }
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Get cart query with selected shipping method
     *
     * @param string $cartId
     * @return string
     */
    private function getCartQuery(string $cartId): string
    {
        return <<<QUERY
            {
              cart(cart_id: "{$cartId}") {
                shipping_addresses {
                  selected_shipping_method {
                    carrier_code
                    carrier_title
                    method_code
                    method_title
                  }
                }
              }
            }
        QUERY;
    }

    /**
     * Get Estimated totals mutation
     *
     * @param string $cartId
     * @param string|null $carrierCode
     * @param string|null $methodCode
     * @return string
     */
    private function getEstimateTotalsMutation(string $cartId, ?string $carrierCode, ?string $methodCode): string
    {
        return <<<MUTATION
            mutation {
              estimateTotals(
                input: {
                  cart_id: "{$cartId}"
                  address: {
                    country_code: US
                    postcode: "10005"
                    region: {
                      region: "NY"
                    }
                  }
                  shipping_method: {
                    carrier_code: "{$carrierCode}"
                    method_code: "{$methodCode}"
                  }
                }
              ) {
                cart {
                  id
                }
              }
            }
        MUTATION;
    }
}
