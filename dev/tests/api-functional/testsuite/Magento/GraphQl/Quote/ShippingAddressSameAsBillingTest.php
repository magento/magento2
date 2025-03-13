<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test same_as_billing field in customerCart.shipping_addresses
 */
class ShippingAddressSameAsBillingTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @var Order
     */
    private $orderModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
        $this->orderModel = Bootstrap::getObjectManager()->get(Order::class);
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testSetSameAsShippingAddressFieldTrue(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $this->graphQlMutation(
            $this->getBillingAddressMutationSameAsShipping($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        $this->assertSameAsBillingField(
            $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $customerAuthHeaders),
            true
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testBillingSetSameAsShippingAddressSave(): void
    {
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();

        //set shipping address to cart (save_in_address_book => true)
        $this->graphQlMutation(
            $this->getSetShippingAddressOnCartMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        //set billing address to cart same as shipping
        $this->graphQlMutation(
            $this->getBillingAddressMutationSameAsShipping($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        //set shipping method on cart
        $this->graphQlMutation(
            $this->getShippingMethodMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        //set payment method on cart
        $this->graphQlMutation(
            $this->getPaymentMethodMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        //place order
        $orderResponse = $this->graphQlMutation(
            $this->getPlaceOrderMutation($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        $order = $this->orderModel->loadByIncrementId($orderNumber);

        self::assertNotNull($order->getShippingAddress()->getCustomerAddressId());

        //Revert order as it is created through mutations and not fixtures
        $this->revertOrder($orderNumber);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
    ]
    public function testSetUseForShippingForBillingAddress(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $this->graphQlMutation(
            $this->getBillingAddressMutationUseForShipping($maskedQuoteId),
            [],
            '',
            $customerAuthHeaders
        );

        $this->assertSameAsBillingField(
            $this->graphQlQuery($this->getQuery($maskedQuoteId), [], '', $customerAuthHeaders),
            true
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
    ]
    public function testShippingAndBillingAddressIsDifferent(): void
    {
        $this->assertSameAsBillingField(
            $this->graphQlQuery(
                $this->getQuery($this->fixtures->get('quoteIdMask')->getMaskedId()),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            ),
            false
        );
    }

    /**
     * Asserts the same_as_billing field in cart.shipping_addresses
     *
     * @param array $response
     * @param bool $sameAsBilling
     * @return void
     */
    private function assertSameAsBillingField(array $response, bool $sameAsBilling): void
    {
        self::assertEquals(
            [
                'cart' => [
                    'shipping_addresses' => [
                        0 => [
                            'same_as_billing' => $sameAsBilling
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    /**
     * Get Set shipping address on cart mutation with save_in_address_book as true
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getSetShippingAddressOnCartMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              setShippingAddressesOnCart(
                input: {
                  cart_id: "{$maskedQuoteId}"
                  shipping_addresses: [
                    {
                      address: {
                        firstname: "John"
                        lastname: "Doe"
                        company: "test"
                        street: ["test", "test"]
                        city: "Atlanta"
                        region: "GA"
                        postcode: "12345"
                        country_code: "US"
                        telephone: "9999999999"
                        save_in_address_book: true
                      },
                    }
                  ]
                }
              ) {
                cart {
                  id
                  shipping_addresses {
                    firstname
                    lastname
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Returns GraphQl mutation for (setBillingAddressOnCart) with same_as_shipping: true
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getBillingAddressMutationSameAsShipping(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              setBillingAddressOnCart(
                input: {
                  cart_id: "{$maskedQuoteId}",
                  billing_address: {
                    same_as_shipping: true
                  }
                }
              ) {
                cart {
                  id
                  shipping_addresses {
                    firstname
                    lastname
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Returns GraphQl mutation for (setBillingAddressOnCart) with use_for_shipping: true
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getBillingAddressMutationUseForShipping(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              setBillingAddressOnCart(
                input: {
                  cart_id: "{$maskedQuoteId}",
                  billing_address: {
                    address: {
                      firstname: "test firstname"
                      lastname: "test lastname"
                      company: "test company"
                      street: ["test street 1", "test street 2"]
                      city: "test city"
                      postcode: "887766"
                      telephone: "88776655"
                      region: "TX"
                      country_code: "US"
                    }
                    use_for_shipping: true
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

    /**
     * Get set shipping method on cart mutation
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getShippingMethodMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              setShippingMethodsOnCart(
                input: {
                  cart_id: "{$maskedQuoteId}",
                  shipping_methods: [
                    {
                      carrier_code: "flatrate"
                      method_code: "flatrate"
                    }
                  ]
                }
              ) {
                cart {
                  id
                }
              }
            }
        MUTATION;
    }

    /**
     * Get set payment method on cart mutation
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPaymentMethodMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              setPaymentMethodOnCart(input: {
                  cart_id: "{$maskedQuoteId}"
                  payment_method: {
                      code: "checkmo"
                  }
              }) {
                cart {
                  selected_payment_method {
                    code
                    title
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Get place order mutation
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderMutation(string $maskedQuoteId): string
    {
        return <<<MUTATION
            mutation {
              placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
                order {
                    order_number
                }
                errors {
                    message
                    code
                }
              }
            }
        MUTATION;
    }

    /**
     * Returns GraphQl query with cart shipping_addresses.same_as_billing field
     *
     * @param string $maskedQuoteId
     * @return string
     */
    private function getQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
            {
              cart(cart_id: "$maskedQuoteId") {
                shipping_addresses {
                  same_as_billing
                }
              }
            }
        QUERY;
    }

    /**
     * Delete Orders from sales_order and sales_order_grid table
     *
     * @param string $orderNumber
     * @return void
     */
    private function revertOrder(string $orderNumber): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName('sales_order'),
            ['increment_id = ?' => $orderNumber]
        );
        $connection->delete(
            $this->resourceConnection->getTableName('sales_order_grid'),
            ['increment_id = ?' => $orderNumber]
        );
    }

    /**
     * Generates token for GQL and returns header with generated token
     *
     * @param string $customerEmail
     * @return array
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     */
    private function getCustomerAuthHeaders(string $customerEmail): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($customerEmail, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
