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
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class AddressBookIdTest extends GraphQlAbstract
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(Customer::class, ['addresses' => [['postcode' => '12345']]], as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$cart.id$'], 'quoteIdMask'),
    ]
    public function testDefaultAddressBookId(): void
    {
        $customer = $this->fixtures->get('customer');
        $addresses = $customer->getAddresses();
        $customerAddress = array_shift($addresses);

        $this->graphQlMutation(
            $this->getSetShippingAddressOnCartMutation(
                $this->fixtures->get('quoteIdMask')->getMaskedId(),
                (int)$customerAddress->getId()
            ),
            [],
            '',
            $this->getCustomerAuthHeaders($customer->getEmail())
        );

        $this->assertEquals(
            [
                "customerCart" => [
                    "shipping_addresses" => [
                        ["id" => (int)$customerAddress->getId()]
                    ],
                    "billing_address" => ["id" => (int)$customerAddress->getId()]
                ]
            ],
            $this->graphQlMutation(
                $this->getCustomerCartQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($customer->getEmail())
            )
        );
    }

    /**
     * @throws Exception
     */
    #[
        DataFixture(Customer::class, ['addresses' => [['postcode' => '12345']]], as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testAddressBookIdForNewQuoteAddress(): void
    {
        $this->assertEquals(
            [
                "customerCart" => [
                    "shipping_addresses" => [
                        ["id" => ""]
                    ],
                    "billing_address" => ["id" => ""]
                ]
            ],
            $this->graphQlMutation(
                $this->getCustomerCartQuery(),
                [],
                '',
                $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
            )
        );
    }

    /**
     * Get setShippingAddressOnCart mutation
     *
     * @param string $cartId
     * @param int $customerAddressId
     * @return string
     */
    private function getSetShippingAddressOnCartMutation(string $cartId, int $customerAddressId): string
    {
        return <<<MUTATION
            mutation {
              setShippingAddressesOnCart(
                input: {
                  cart_id: "{$cartId}"
                  shipping_addresses: {
                     customer_address_id: "$customerAddressId"
                  }
                }
              ) {
                cart {
                  billing_address {
                    __typename
                  }
                }
              }
            }
        MUTATION;
    }

    /**
     * Get Customer Cart Query
     *
     * @return string
     */
    private function getCustomerCartQuery(): string
    {
        return <<<QUERY
            {
              customerCart {
                shipping_addresses {
                  id
                }
                billing_address {
                  id
                }
              }
            }
        QUERY;
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
        return [
            'Authorization' => 'Bearer ' . $this->customerTokenService->createCustomerAccessToken(
                $customerEmail,
                'password'
            )
        ];
    }
}
