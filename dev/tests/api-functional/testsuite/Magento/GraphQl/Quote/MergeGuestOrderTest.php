<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote;

use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;

class MergeGuestOrderTest extends GraphQlAbstract
{
    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteIdInterface;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->quoteIdToMaskedQuoteIdInterface = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class);
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        /** @var ScopeConfigInterface $scopeConfig */
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testMergeOrderInCreateAccount()
    {
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());

        // Place guest order
        $query = $this->getPlaceOrderQuery($maskedQuoteId);
        $placeOrderResponse = $this->graphQlMutation($query);
        $guestEmail = $placeOrderResponse['placeOrder']['orderV2']['email'];

        // Create account with guest email id
        $query = $this->getCreateAccountQuery($guestEmail);
        $this->graphQlMutation($query);

        // Fetch guest order after create account
        $orderNumber = $placeOrderResponse['placeOrder']['orderV2']['number'];
        $query = $this->getCustomerOrderQuery($orderNumber);
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap($guestEmail));

        $guestCustomerOrder = $response['customer']['orders']['items'];

        $this->assertEquals(1, count($guestCustomerOrder));
        $this->assertEquals($guestEmail, $guestCustomerOrder[0]['email']);
        $this->assertEquals($orderNumber, $guestCustomerOrder[0]['number']);
    }

    #[
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(Customer::class, ['email' => 'customer1@example.com', 'password' => 'test123#'], as: 'customer'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test_quote'], as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, ['cart_id' => '$cart.id$', 'email' => 'customer1@example.com']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testMergeOrderInPlaceGuestOrder()
    {
        $customerEmail = DataFixtureStorageManager::getStorage()->get('customer')->getEmail();
        $query = $this->getCustomerOrderQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap($customerEmail));

        // Assert before place order
        $customerOrder = $response['customer']['orders']['items'];
        $this->assertEquals(0, count($customerOrder));

        // Place guest order
        $cart = DataFixtureStorageManager::getStorage()->get('cart');
        $maskedQuoteId = $this->quoteIdToMaskedQuoteIdInterface->execute((int) $cart->getId());
        $query = $this->getPlaceOrderQuery($maskedQuoteId);
        $this->graphQlMutation($query);

        // Assert before place order
        $query = $this->getCustomerOrderQuery();
        $response = $this->graphQlQuery($query, [], '', $this->getHeaderMap($customerEmail));
        $customerOrder = $response['customer']['orders']['items'];

        $this->assertEquals(1, count($customerOrder));
        $this->assertEquals($customerEmail, $customerOrder[0]['email']);
    }

    private function getCustomerOrderQuery(string $orderNumber = '')
    {
        if ($orderNumber) {
            return <<<QUERY
query {
  customer {
    orders(
    filter:{number:{eq:"{$orderNumber}"}}
    ) {
      items {
        email,
        number
      }
    }
  }
}
QUERY;
        } else {
            return <<<QUERY
query {
  customer {
    orders(
    currentPage:1
    ) {
      items {
        email,
        number
      }
    }
  }
}
QUERY;
        }
    }

    /**
     * @param string $email
     * @return string
     */
    private function getCreateAccountQuery(string $email): string
    {
        return <<<QUERY
mutation {
  createCustomer(
    input: {
      firstname: "Fname"
      lastname: "Lname"
      email: "{$email}"
      password: "test123#"
    }
  ) {
    customer {
      firstname
      lastname
      is_subscribed
    }
  }
}
QUERY;
    }

    /**
     * @param string $maskedQuoteId
     * @return string
     */
    private function getPlaceOrderQuery(string $maskedQuoteId): string
    {
        return <<<QUERY
mutation {
  placeOrder(input: {cart_id: "{$maskedQuoteId}"}) {
    orderV2 {
      number,
      email
    }
    errors {
      message
      code
    }
  }
}
QUERY;
    }

    /**
     * @param string $username
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getHeaderMap(string $username, string $password = 'test123#'): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($username, $password);
        $headerMap = ['Authorization' => 'Bearer ' . $customerToken];
        return $headerMap;
    }
}
