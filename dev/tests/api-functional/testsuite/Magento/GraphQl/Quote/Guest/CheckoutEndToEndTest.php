<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Quote\Guest;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;

/**
 * End to checkout tests for guest
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
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = Bootstrap::getObjectManager();
        $this->registry = $objectManager->get(Registry::class);
        $this->quoteCollectionFactory = $objectManager->get(QuoteCollectionFactory::class);
        $this->quoteResource = $objectManager->get(QuoteResource::class);
        $this->quoteIdMaskFactory = $objectManager->get(QuoteIdMaskFactory::class);
        $this->orderCollectionFactory = $objectManager->get(CollectionFactory::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * @magentoConfigFixture default_store checkout/options/guest_checkout 1
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testCheckoutWorkflow()
    {
        $quantity = 2;

        $sku = $this->findProduct();
        $cartId = $this->createEmptyCart();
        $this->setGuestEmailOnCart($cartId);
        $this->addProductToCart($cartId, $quantity, $sku);

        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);

        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);

        $this->placeOrder($cartId);
    }

    /**
     * Test checkout workflow with null second street in shipping address
     * Validates that null values in street array are properly filtered and don't cause errors
     */
    #[
        Config("checkout/options/guest_checkout", "1", "store", "default"),
        DataFixture(
            ProductFixture::class,
            ['price' => 1, 'name' => 'simple1', 'sku' => 'simple1'],
            'simple1'
        )
    ]
    public function testCheckoutWithNullSecondStreetInShippingAddress()
    {
        $cartId = $this->createEmptyCart();
        $this->setGuestEmailOnCart($cartId);
        $this->addProductToCart($cartId, 1, $this->findProduct());

        $shippingMethod = $this->setAndVerifyShippingAddressWithNullSecondStreet($cartId);

        $this->setBillingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $this->placeOrder($cartId);
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
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('createEmptyCart', $response);
        self::assertNotEmpty($response['createEmptyCart']);

        return $response['createEmptyCart'];
    }

    /**
     * @param string $cartId
     * @return void
     */
    private function setGuestEmailOnCart(string $cartId): void
    {
        $query = <<<QUERY
mutation {
  setGuestEmailOnCart(
    input: {
      cart_id: "{$cartId}"
      email: "customer@example.com"
    }
  ) {
    cart {
      email
    }
  }
}
QUERY;
        $this->graphQlMutation($query);
    }

    /**
     * @param string $cartId
     * @param float $quantity
     * @param string $sku
     * @return void
     */
    private function addProductToCart(string $cartId, float $quantity, string $sku): void
    {
        $query = <<<QUERY
mutation {
  addSimpleProductsToCart(
    input: {
      cart_id: "{$cartId}"
      cart_items: [
        {
          data: {
            quantity: {$quantity}
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
        $this->graphQlMutation($query);
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
        $this->graphQlMutation($query);
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
        $response = $this->graphQlMutation($query);
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
        $response = $this->graphQlMutation($query);
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
        $this->graphQlMutation($query);
    }

    /**
     * @param string $cartId
     * @return void
     */
    private function placeOrder(string $cartId): void
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
        $response = $this->graphQlMutation($query);
        self::assertArrayHasKey('placeOrder', $response);
        self::assertArrayHasKey('order', $response['placeOrder']);
        self::assertArrayHasKey('order_number', $response['placeOrder']['order']);
        self::assertNotEmpty($response['placeOrder']['order']['order_number']);
    }

    /**
     * Set shipping address with null second street in address
     *
     * @param string $cartId
     * @return array
     */
    private function setAndVerifyShippingAddressWithNullSecondStreet(string $cartId): array
    {
        $query = <<<QUERY
            mutation {
              setShippingAddressesOnCart(
                input: {
                  cart_id: "$cartId"
                  shipping_addresses: [
                    {
                      address: {
                        firstname: "B"
                        lastname: "C"
                        street: ["Lorem ipsum dolor sit ame", null]
                        city: "PARIS"
                        postcode: "56590"
                        country_code: "FR"
                        telephone: "0601020304"
                        save_in_address_book: false
                      }
                    }
                  ]
                }
              ) {
                cart {
                  shipping_addresses {
                    firstname
                    lastname
                    company
                    street
                    city
                    postcode
                    country {
                      code
                      label
                    }
                    telephone
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

        $response = $this->graphQlMutation($query);

        $this->assertArrayHasKey('setShippingAddressesOnCart', $response);
        $this->assertArrayHasKey('cart', $response['setShippingAddressesOnCart']);
        $this->assertArrayHasKey('shipping_addresses', $response['setShippingAddressesOnCart']['cart']);
        $this->assertCount(1, $response['setShippingAddressesOnCart']['cart']['shipping_addresses']);

        $shippingAddress = current($response['setShippingAddressesOnCart']['cart']['shipping_addresses']);

        $this->assertEquals('B', $shippingAddress['firstname']);
        $this->assertEquals('C', $shippingAddress['lastname']);
        $this->assertNull($shippingAddress['company']);
        $this->assertEquals(['Lorem ipsum dolor sit ame'], $shippingAddress['street']);
        $this->assertEquals('PARIS', $shippingAddress['city']);
        $this->assertEquals('56590', $shippingAddress['postcode']);
        $this->assertEquals('FR', $shippingAddress['country']['code']);
        $this->assertEquals('FR', $shippingAddress['country']['label']);
        $this->assertEquals('0601020304', $shippingAddress['telephone']);

        $this->assertArrayHasKey('available_shipping_methods', $shippingAddress);
        $this->assertGreaterThan(0, count($shippingAddress['available_shipping_methods']));

        $availableShippingMethod = current($shippingAddress['available_shipping_methods']);
        $this->assertArrayHasKey('carrier_code', $availableShippingMethod);
        $this->assertNotEmpty($availableShippingMethod['carrier_code']);
        $this->assertArrayHasKey('method_code', $availableShippingMethod);
        $this->assertNotEmpty($availableShippingMethod['method_code']);

        return $availableShippingMethod;
    }

    protected function tearDown(): void
    {
        $this->deleteQuote();
        $this->deleteOrder();
        parent::tearDown();
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
