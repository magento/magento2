<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * End to checkout tests for customer
 */
class CheckoutEndToEndTest extends GraphQlAbstract
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->registry = $objectManager->get(Registry::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->orderFactory = $objectManager->get(OrderFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testCheckoutWorkflow()
    {
        $quantity = 2;

        $this->createCustomer();
        $token = $this->loginCustomer();
        $this->headers = ['Authorization' => 'Bearer ' . $token];

        $sku = $this->findProduct();
        $cartId = $this->createEmptyCart();
        $this->addProductToCart($cartId, $quantity, $sku);

        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);

        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);

        $orderIncrementId = $this->placeOrder($cartId);

        $order = $this->orderFactory->create();
        $order->loadByIncrementId($orderIncrementId);

        $this->checkOrderInHistory($orderIncrementId);
        $this->assertNotEmpty($order->getEmailSent());
    }

    /**
     * @return void
     */
    private function createCustomer(): void
    {
        $query = <<<QUERY
mutation {
  createCustomer(
    input: {
      firstname: "endto"
      lastname: "endtester"
      email: "customer@example.com"
      password: "123123Qa"
    }
  ) {
    customer {
      id
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @return string
     */
    private function loginCustomer(): string
    {
        $query = <<<QUERY
mutation {
  generateCustomerToken(
    email: "customer@example.com"
    password: "123123Qa"
  ) {
    token
  }
}
QUERY;
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('generateCustomerToken', $response);
        self::assertArrayHasKey('token', $response['generateCustomerToken']);
        self::assertNotEmpty($response['generateCustomerToken']['token']);

        return $response['generateCustomerToken']['token'];
    }

    /**
     * @return string
     */
    private function findProduct(): string
    {
        $query = <<<QUERY
{
  products (
    filter: {
      sku: {
        eq:"simple1"
      }
    }
    pageSize: 1
    currentPage: 1
  ) {
    items {
      sku
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        self::assertArrayHasKey('products', $response);
        self::assertArrayHasKey('items', $response['products']);
        self::assertCount(1, $response['products']['items']);

        $product = current($response['products']['items']);
        self::assertArrayHasKey('sku', $product);
        self::assertNotEmpty($product['sku']);

        return $product['sku'];
    }

    /**
     * @return string
     */
    private function createEmptyCart(): string
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->headers);
        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        return $response['createEmptyCart'];
    }

    /**
     * @param string $cartId
     * @param float $qty
     * @param string $sku
     * @return void
     */
    private function addProductToCart(string $cartId, float $qty, string $sku): void
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "{$cartId}"
      cart_items: [
        {
          data: {
            quantity: {$qty}
            sku: "{$sku}"
          }
        }
      ]
    }
  ) {
    cart {
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
        $this->graphQlMutation($query, [], '', $this->headers);
    }

    /**
     * @param string $cartId
     * @param array $auth
     * @return array
     */
    private function setBillingAddress(string $cartId): void
    {
        $query = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$cartId}"
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
QUERY;
        $this->graphQlMutation($query, [], '', $this->headers);
    }

    /**
     * @param string $cartId
     * @return array
     */
    private function setShippingAddress(string $cartId): array
    {
        $query = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$cartId"
      shipping_addresses: [
        {
          address: {
            firstname: "test firstname"
            lastname: "test lastname"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "test city"
            region: "TX"
            postcode: "887766"
            country_code: "US"
            telephone: "88776655"
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        available_shipping_methods {
          carrier_code
          method_code
          amount {
            value
          }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->headers);
        self::assertArrayHasKey('setShippingAddressesOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        self::assertArrayHasKey('shipping_addresses', $response['setShippingAddressesOnCart']['cart']);
        self::assertCount(1, $response['setShippingAddressesOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingAddressesOnCart']['cart']['shipping_addresses']);
        self::assertArrayHasKey('available_shipping_methods', $shippingAddress);
        self::assertCount(1, $shippingAddress['available_shipping_methods']);

        $availableShippingMethod = current($shippingAddress['available_shipping_methods']);
        self::assertArrayHasKey('carrier_code', $availableShippingMethod);
        self::assertNotEmpty($availableShippingMethod['carrier_code']);

        self::assertArrayHasKey('method_code', $availableShippingMethod);
        self::assertNotEmpty($availableShippingMethod['method_code']);

        self::assertArrayHasKey('amount', $availableShippingMethod);
        self::assertArrayHasKey('value', $availableShippingMethod['amount']);
        self::assertNotEmpty($availableShippingMethod['amount']['value']);

        return $availableShippingMethod;
    }

    /**
     * @param string $cartId
     * @param array $method
     * @return array
     */
    private function setShippingMethod(string $cartId, array $method): array
    {
        $query = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
    cart_id: "{$cartId}",
    shipping_methods: [
      {
         carrier_code: "{$method['carrier_code']}"
         method_code: "{$method['method_code']}"
      }
    ]
  }) {
    cart {
      available_payment_methods {
        code
        title
      }
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->headers);
        self::assertArrayHasKey('setShippingMethodsOnCart', $response);
        self::assertArrayHasKey('cart', $response['setShippingMethodsOnCart']);
        self::assertArrayHasKey('available_payment_methods', $response['setShippingMethodsOnCart']['cart']);
        self::assertCount(1, $response['setShippingMethodsOnCart']['cart']['available_payment_methods']);

        $availablePaymentMethod = current($response['setShippingMethodsOnCart']['cart']['available_payment_methods']);
        self::assertArrayHasKey('code', $availablePaymentMethod);
        self::assertNotEmpty($availablePaymentMethod['code']);
        self::assertArrayHasKey('title', $availablePaymentMethod);
        self::assertNotEmpty($availablePaymentMethod['title']);

        return $availablePaymentMethod;
    }

    /**
     * @param string $cartId
     * @param array $method
     * @return void
     */
    private function setPaymentMethod(string $cartId, array $method): void
    {
        $query = <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$cartId}"
      payment_method: {
        code: "{$method['code']}"
      }
    }
  ) {
    cart {
      selected_payment_method {
        code
      }
    }
  }
}
QUERY;
        $this->graphQlMutation($query, [], '', $this->headers);
    }

    /**
     * @param string $cartId
     * @return string
     */
    private function placeOrder(string $cartId): string
    {
        $query = <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$cartId}"
    }
  ) {
    order {
      order_number
    }
  }
}
QUERY;
        $response = $this->graphQlMutation($query, [], '', $this->headers);
        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order', $response['placeOrder']);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertNotEmpty($response['placeOrder']['order']['order_number']);

        return $response['placeOrder']['order']['order_number'];
    }

    /**
     * @param string $orderId
     * @return void
     */
    private function checkOrderInHistory(string $orderId): void
    {
        $query = <<<QUERY
{
  customerOrders {
    items {
      increment_id
      grand_total
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query, [], '', $this->headers);
        self::assertArrayHasKey('customerOrders', $response);
        self::assertArrayHasKey('items', $response['customerOrders']);
        self::assertCount(1, $response['customerOrders']['items']);

        $order = current($response['customerOrders']['items']);
        self::assertArrayHasKey('increment_id', $order);
        self::assertEquals($orderId, $order['increment_id']);

        self::assertArrayHasKey('grand_total', $order);
    }

    protected function tearDown(): void
    {
        $this->deleteCustomer();
        $this->deleteQuote();
        $this->deleteOrder();
        parent::tearDown();
    }

    /**
     * @return void
     */
    private function deleteCustomer(): void
    {
        $email = 'customer@example.com';
        try {
            $customer = $this->customerRepository->get($email);
        } catch (\Exception $exception) {
            return;
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        $this->customerRepository->delete($customer);
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * @return void
     */
    private function deleteQuote(): void
    {
        $quoteCollection = $this->quoteCollectionFactory->create();
        foreach ($quoteCollection as $quote) {
            $this->quoteResource->delete($quote);

            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quote->getId())
                ->delete();
        }
    }

    /**
     * @return void
     */
    private function deleteOrder()
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        $orderCollection = $this->orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }
}
