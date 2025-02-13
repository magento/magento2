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
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtures = Bootstrap::getObjectManager()->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
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
    public function testSetSameAsShipping(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $headerMap = $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail());

        $this->graphQlMutation(
            $this->getBillingAddressMutationSameAsShipping($maskedQuoteId),
            [],
            '',
            $headerMap
        );

        $this->assertSameAsBillingField(
            $this->graphQlQuery(
                $this->getQuery($maskedQuoteId),
                [],
                '',
                $headerMap
            ),
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
    public function testSetUseForShipping(): void
    {
        $maskedQuoteId = $this->fixtures->get('quoteIdMask')->getMaskedId();
        $headerMap = $this->getCustomerAuthHeaders(
            $this->fixtures->get('customer')->getEmail()
        );

        $this->graphQlMutation(
            $this->getBillingAddressMutationUseForShipping($maskedQuoteId),
            [],
            '',
            $headerMap
        );

        $this->assertSameAsBillingField(
            $this->graphQlQuery(
                $this->getQuery($maskedQuoteId),
                [],
                '',
                $headerMap
            ),
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
            'Authorization' => 'Bearer ' .
                $this->customerTokenService->createCustomerAccessToken($customerEmail, 'password')
        ];
    }
}
