<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Quote\Customer;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * End to checkout tests for customers and guests
 */
class EndToEndCheckoutTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testCheckoutAsCustomer()
    {
        $email = 'e2e_1@example.com';

        $this->graphQlMutation($this->buildCreateCustomerMutation($email));
        $authHeader = $this->createAuthHeader($email);

        $cartId = $this->createEmptyCart($authHeader);
        $cart = $this->configureQuote($cartId, $authHeader);

        $placeOrderResult = $this->graphQlMutation($this->buildPlaceOrderMutation($cartId), [], '', $authHeader);
        $orderId = $placeOrderResult['placeOrder']['order']['order_id'];
        $this->assertNotEmpty($orderId);

        $order = $this->getOrderFromHistory($orderId, $authHeader);
        $this->assertEquals($cart['prices']['grand_total']['value'], $order['grand_total']);
        //TODO: Make additional assertions when order properties are added
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_layered_navigation_attribute.php
     */
    public function testCheckoutAsGuest()
    {
        $email = 'e2e_2@example.com';
        $cartId = $this->createEmptyCart();
        $this->graphQlMutation($this->buildSetGuestEmailOnCartMutation($cartId, $email));
        $this->configureQuote($cartId);

        $placeOrderResult = $this->graphQlMutation($this->buildPlaceOrderMutation($cartId));
        $orderId = $placeOrderResult['placeOrder']['order']['order_id'];

        $this->assertNotEmpty($orderId);
    }

    /**
     * Configures cart with order placement requirements
     *
     * @param string $cartId
     * @param array $headers
     * @return array
     */
    private function configureQuote(string $cartId, array $headers = []): array
    {
        $expectedTotal = 5.99;
        $expectedQty = 1;

        $sku = $this->getSku($headers);
        $addToCartResult = $this->graphQlMutation($this->buildAddToCartMutation($cartId, $expectedQty, $sku), [], '', $headers);
        $cart = $addToCartResult['addSimpleProductsToCart']['cart'];
        $this->assertGrandTotal($expectedTotal, $cart);

        $address = $this->setShippingAddress($cartId, $headers);
        $shippingMethod = $this->extractFirstAvailableShippingMethod($address);

        $cart = $this->setShippingMethod($cartId, $shippingMethod, $address, $headers);
        $expectedTotal += $shippingMethod['amount'];
        $this->assertGrandTotal($expectedTotal, $cart);
        $this->assertSelectedShippingMethod($shippingMethod, $cart);

        $setBillingAddressResult = $this->graphQlMutation($this->buildSetNewBillingAddressMutation($cartId), [], '', $headers);
        $cart = $setBillingAddressResult['setBillingAddressOnCart']['cart'];
        $paymentMethod = $this->extractFirstAvailablePaymentMethod($cart);

        $setPaymentResult = $this->graphQlMutation($this->buildSetPaymentMethodMutation($cartId, $paymentMethod), [], '', $headers);
        $cart = $setPaymentResult['setPaymentMethodOnCart']['cart'];
        $this->assertPaymentMethod($paymentMethod, $cart);
        $this->assertGrandTotal($expectedTotal, $cart);

        return $cart;
    }

    /**
     * Generates customer authentication header for restricted requests
     *
     * @param string $email
     * @return array
     */
    private function createAuthHeader(string $email): array
    {
        $result = $this->graphQlMutation($this->buildLoginMutation($email));
        $token = $result['generateCustomerToken']['token'];

        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Creates empty cart for customer or guest
     *
     * @param array $auth
     * @return string
     */
    private function createEmptyCart(array $auth = []): string
    {
        $query = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;

        $result = $this->graphQlMutation($query, [], '', $auth);

        return $result['createEmptyCart'];
    }

    /**
     * Get first SKU returned by catalog search
     *
     * @param array $auth
     * @return string
     */
    private function getSku(array $auth): string
    {
        $result = $this->graphQlQuery($this->buildProductSearchQuery('simple'), [], '', $auth);
        $items = $result['products']['items'];
        $item = current($items);

        return $item['sku'];
    }

    /**
     * Set cart shipping address
     *
     * @param string $cartId
     * @param array $auth
     * @return array
     */
    private function setShippingAddress(string $cartId, array $auth): array
    {
        $result = $this->graphQlMutation($this->buildSetNewShippingAddressMutation($cartId), [], '', $auth);
        $addresses = $result['setShippingAddressesOnCart']['cart']['shipping_addresses'];

        return current($addresses);
    }

    /**
     * Set cart shipping method
     *
     * @param string $cartId
     * @param array $method
     * @param array $address
     * @param array $auth
     * @return array
     */
    private function setShippingMethod(string $cartId, array $method, array $address, array $auth): array
    {
        $result = $this->graphQlMutation($this->buildSetShippingMethodMutation($cartId, $method, $address), [], '', $auth);

        return $result['setShippingMethodsOnCart']['cart'];
    }

    /**
     * Get order from history by increment id
     *
     * @param string $orderId
     * @param array $auth
     * @return array
     */
    private function getOrderFromHistory(string $orderId, array $auth): array
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

        $result = $this->graphQlQuery($query, [], '', $auth);
        $orders = $result['customerOrders']['items'];

        foreach ($orders as $order) {
            if ($order['increment_id'] === $orderId) {
                return $order;
            }
        }

        $this->fail(sprintf('No order with increment_id: %s', $orderId));
    }

    /**
     * Get first shipping method available from address
     * @param array $address
     * @return array
     */
    private function extractFirstAvailableShippingMethod(array $address): array
    {
        $methods = $address['available_shipping_methods'];

        return current($methods);
    }

    /**
     * Get first payment method available from cart
     *
     * @param array $cart
     * @return array
     */
    private function extractFirstAvailablePaymentMethod(array $cart): array
    {
        $methods = $cart['available_payment_methods'];

        return current($methods);
    }

    /**
     * Assert cart grand total
     *
     * @param float $expected
     * @param array $cart
     */
    private function assertGrandTotal(float $expected, array $cart): void
    {
        $this->assertEquals($expected, $cart['prices']['grand_total']['value']);
    }

    /**
     * Assert cart payment method
     * @param array $method
     * @param array $cart
     */
    private function assertPaymentMethod(array $method, array $cart): void
    {
        $this->assertEquals($method['code'], $cart['selected_payment_method']['code']);
    }

    /**
     * Assert cart shipping method
     *
     * @param array $expectedMethod
     * @param array $cart
     */
    private function assertSelectedShippingMethod(array $expectedMethod, array $cart): void
    {
        $address = current($cart['shipping_addresses']);
        $selectedMethod = $address['selected_shipping_method'];

        $this->assertEquals($expectedMethod['carrier_code'], $selectedMethod['carrier_code']);
        $this->assertEquals($expectedMethod['method_code'], $selectedMethod['method_code']);
    }

    /**
     * Build createCustomer mutation
     *
     * @param string $email
     * @return string
     */
    private function buildCreateCustomerMutation(string $email): string
    {
        return <<<QUERY
mutation {
  createCustomer(
    input: {
      firstname: "endto"
      lastname: "endtester"
      email: "{$email}"
      password: "123123Qr"
    }
  ) {
    customer {
      id
      firstname
      lastname
      email
    }
  }
}
QUERY;
    }

    /**
     * Build generateCustomerToken mutation
     *
     * @param string $email
     * @return string
     */
    private function buildLoginMutation(string $email): string
    {
        return <<<QUERY
mutation {
  generateCustomerToken(
    email: "{$email}"
    password: "123123Qr"
  ){
    token
  }
}
QUERY;
    }

    /**
     * Build product search mutation
     *
     * @param string $term
     * @return string
     */
    private function buildProductSearchQuery(string $term): string
    {
        return <<<QUERY
{
  products (
    filter: {
      sku: {
        like:"{$term}%"
      }
    }
    pageSize: 20
    currentPage: 1
  ) {
    items {
      sku
    }
  }
}
QUERY;
    }

    /**
     * Build addSimpleProductsToCart mutation
     *
     * @param string $cartId
     * @param float $qty
     * @param string $sku
     * @return string
     */
    private function buildAddToCartMutation(string $cartId, float $qty, string $sku): string
    {
        return <<<QUERY
mutation {  
  addSimpleProductsToCart(
    input: {
      cart_id: "{$cartId}"
      cartItems: [
        {
          data: {
            qty: {$qty}
            sku: "{$sku}"
          }
        }
      ]
    }
  ) {
    cart {
      items {
        qty
        product {
          sku
        }
      }
      prices {
        grand_total {
          value,
          currency
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Build setShippingAddressesOnCart mutation
     *
     * @param string $cartId
     * @param bool $save
     * @return string
     */
    private function buildSetNewShippingAddressMutation(string $cartId, bool $save = false): string
    {
        $save = json_encode($save);
        return <<<QUERY
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
            save_in_address_book: {$save}
          }
        }
      ]
    }
  ) {
    cart {
      shipping_addresses {
        address_id
        available_shipping_methods {
          carrier_code
          method_code
          amount
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Build setShippingMethodsOnCart mutation
     *
     * @param string $cartId
     * @param array $method
     * @param array $address
     * @return string
     */
    private function buildSetShippingMethodMutation(string $cartId, array $method, array $address): string
    {
        return <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
    cart_id: "{$cartId}", 
    shipping_methods: [
      {
         cart_address_id: {$address['address_id']}
         carrier_code: "{$method['carrier_code']}"
         method_code: "{$method['method_code']}"
      }
    ]
  }) {
    cart {
      prices {
        grand_total {
          value,
          currency
        }
      }
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
        }
      }
    }
  }
}
QUERY;

    }

    /**
     * Build setBillingAddressOnCart mutation
     *
     * @param string $cartId
     * @param bool $save
     * @return string
     */
    private function buildSetNewBillingAddressMutation(string $cartId, bool $save = false): string
    {
        $save = json_encode($save);
        return <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$cartId}"
      billing_address: {
         address: {
          firstname: "test firstname"
          lastname: "test lastname"
          street: ["test street 1", "test street 2"]
          city: "test city"
          region: "TX"
          postcode: "887766"
          country_code: "US"
          telephone: "88776655"
          save_in_address_book: {$save}
         }
      }
    }
  ) {
    cart {
      available_payment_methods {
        code
        title
      }
      billing_address {
        firstname
        lastname
        company
        street
        city
        postcode
        telephone
        country {
          code
          label
        }
        address_type
      }
    }
  }
}
QUERY;
    }

    /**
     * Build setPaymentMethodOnCart mutation
     *
     * @param string $cartId
     * @param array $payment
     * @return string
     */
    private function buildSetPaymentMethodMutation(string $cartId, array $payment): string
    {
        return <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$cartId}"
      payment_method: {
        code: "{$payment['code']}"
      }
    }
  ) {
    cart {
      items {
        qty
        product {
          sku
        }
      }
      shipping_addresses {
        selected_shipping_method {
          carrier_code
          method_code
        }
      }
      selected_payment_method {
        code
      }
      prices {
        grand_total {
          value
          currency
        }
      }
    }
  }
}
QUERY;
    }

    /**
     * Build setGuestEmailOnCart mutation
     *
     * @param string $cartId
     * @param string $email
     * @return string
     */
    private function buildSetGuestEmailOnCartMutation(string $cartId, string $email): string
    {
        return <<<QUERY
mutation {
  setGuestEmailOnCart(
    input: {
      cart_id: "{$cartId}"
      email: "{$email}"
    }
  ) {
    cart {
      email
    }
  }
}
QUERY;
    }

    /**
     * Build placeOrder mutation
     *
     * @param string $cartId
     * @return string
     */
    private function buildPlaceOrderMutation(string $cartId): string
    {
        return <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$cartId}"
    }
  ) {
    order {
      order_id
    }
  }
}
QUERY;
    }
}
