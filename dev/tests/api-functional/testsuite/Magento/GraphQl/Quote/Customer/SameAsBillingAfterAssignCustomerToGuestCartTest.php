<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask as QuoteIdMaskFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Verify that assigning a guest cart to a customer keeps a shipping address
 * that differs from billing (same_as_billing = false).
 */
class SameAsBillingAfterAssignCustomerToGuestCartTest extends GraphQlAbstract
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
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(QuoteIdMaskFixture::class, ['cart_id' => '$cart.id$'], as: 'mask'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
        DataFixture(CustomerFixture::class, as: 'customer'),
    ]
    public function testSameAsBillingStaysFalseAfterAssignCustomerToGuestCart(): void
    {
        $maskedQuoteId = $this->fixtures->get('mask')->getMaskedId();

        $this->graphQlMutation(
            $this->getSetShippingAddressMutation($maskedQuoteId)
        );

        $this->graphQlMutation(
            $this->getSetBillingAddressMutation($maskedQuoteId)
        );

        $response = $this->graphQlMutation(
            $this->getAssignCustomerToGuestCartMutation($maskedQuoteId),
            [],
            '',
            $this->getCustomerAuthHeaders($this->fixtures->get('customer')->getEmail())
        );

        $cartResponse = $response['assignCustomerToGuestCart'];
        $shippingAddress = current($cartResponse['shipping_addresses']);
        self::assertFalse($shippingAddress['same_as_billing']);
        self::assertNotSame(
            $shippingAddress['firstname'],
            $cartResponse['billing_address']['firstname']
        );
    }

    /**
     * @return string
     */
    private function getSetShippingAddressMutation(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      shipping_addresses: [
        {
          address: {
            firstname: "John"
            lastname: "Doe"
            telephone: "0987654321"
            street: ["Green str, 67"]
            city: "CityM"
            region: "AL"
            postcode: "75477"
            country_code: "US"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        firstname
        same_as_billing
      }
    }
  }
}
QUERY;
    }

    /**
     * @return string
     */
    private function getSetBillingAddressMutation(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$maskedQuoteId}"
      billing_address: {
        address: {
          firstname: "Jane"
          lastname: "Roe"
          telephone: "1234567890"
          street: ["Test street 1"]
          city: "Test city"
          region: "CA"
          postcode: "887766"
          country_code: "US"
        }
      }
    }
  ) {
    cart {
      shipping_addresses {
        firstname
        same_as_billing
      }
    }
  }
}
QUERY;
    }

    /**
     * @return string
     */
    private function getAssignCustomerToGuestCartMutation(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  assignCustomerToGuestCart(cart_id: "{$maskedQuoteId}") {
    shipping_addresses {
      firstname
      same_as_billing
    }
    billing_address {
      firstname
    }
  }
}
QUERY;
    }

    /**
     * @return array
     */
    private function getCustomerAuthHeaders(string $email): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, 'password');
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
