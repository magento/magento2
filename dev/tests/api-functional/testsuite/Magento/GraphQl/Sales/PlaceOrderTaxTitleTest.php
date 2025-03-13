<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Exception;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Tax\Test\Fixture\ProductTaxClass as ProductTaxClassFixture;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Test\Fixture\TaxRule as TaxRuleFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class PlaceOrderTaxTitleTest extends GraphQlAbstract
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
        $this->resourceConnection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(ProductTaxClassFixture::class, as: 'product_tax_class'),
        DataFixture(TaxRateFixture::class, as: 'rate'),
        DataFixture(
            TaxRuleFixture::class,
            [
                'customer_tax_class_ids' => [3],
                'product_tax_class_ids' => ['$product_tax_class.classId$'],
                'tax_rate_ids' => ['$rate.id$']
            ],
            'rule'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'custom_attributes' => ['tax_class_id' => '$product_tax_class.classId$']
            ],
            'product'
        ),
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], as: 'quote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$quote.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask')
    ]
    public function testTaxTitleOnPlaceOrder(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $customerAuthHeaders = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

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

        //assert tax title
        self::assertNotNull($orderResponse['placeOrder']['orderV2']['total']['taxes'][0]['title']);
        self::assertNotEmpty($orderResponse['placeOrder']['orderV2']['total']['taxes'][0]['title']);

        //Revert order as it is created through mutations and not fixtures
        $this->revertOrder($orderResponse['placeOrder']['order']['order_number']);
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
                        city: "New York"
                        region: "NY"
                        postcode: "10001"
                        country_code: "US"
                        telephone: "9999999999"
                        save_in_address_book: false
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
     * Get place order mutation with orderV2 data
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
                orderV2 {
                    total {
                        taxes {
                            amount {
                            value
                            currency
                        }
                        title
                        }
                    }
                }
              }
            }
        MUTATION;
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
