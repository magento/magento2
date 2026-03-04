<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales\Order;

use Magento\TestFramework\TestCase\GraphQl\Client;

class Create
{
    /**
     * @param Client $gqlClient
     */
    public function __construct(
        private readonly Client $gqlClient
    ) {
    }

    /**
     * Make GraphQl POST request
     *
     * @param string $query
     * @return array
     */
    private function makeRequest(string $query): array
    {
        return $this->gqlClient->post($query, [], '', []);
    }

    /**
     * Place order if cart have products
     *
     * @param string $cartId
     * @return array
     */
    public function placeOrder(string $cartId): array
    {
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->getAvailableShippingMethods($cartId);
        $paymentMethod = $this->getAvailablePaymentMethods($shippingMethod, $cartId);
        $this->setPaymentMethod($paymentMethod, $cartId);
        return $this->doPlaceOrder($cartId);
    }

    /**
     * Set the billing address on the cart
     *
     * @param string $cartId
     * @param array|null $addressData
     * @return array
     */
    public function setBillingAddress(string $cartId, ?array $addressData = null): array
    {
        $address = $this->getAddress($addressData);

        $setBillingAddress = <<<MUTATION
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$cartId}"
      billing_address: {
         {$address}
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
        return $this->makeRequest($setBillingAddress);
    }

    /**
     * Set the shipping address on the cart and return an available shipping method
     *
     * @param string $cartId
     * @param array|null $addressData
     * @return array
     */
    public function getAvailableShippingMethods(string $cartId, ?array $addressData = null): array
    {
        $address = $this->getAddress($addressData);
        $setShippingAddress = <<<MUTATION
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "{$cartId}"
      shipping_addresses: [
        {
          {$address}
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
MUTATION;
        $result = $this->makeRequest($setShippingAddress);
        $shippingMethod = $result['setShippingAddressesOnCart']
        ['cart']['shipping_addresses'][0]['available_shipping_methods'][0];
        return $shippingMethod;
    }

    /**
     * Get address query from array
     *
     * @param array|null $addressData
     * @return string
     */
    private function getAddress(?array $addressData = null): String
    {
        $telephone = $addressData['telephone'] ?? '5123456677';
        $firstName = $addressData['firstname'] ?? 'test shipFirst';
        $lastName = $addressData['lastname'] ?? 'test shipLast';
        $company = $addressData['company'] ?? 'Test company';
        $region = $addressData['region'] ?? 'AL';
        $countryCode = $addressData['country_code'] ?? 'US';
        $postCode = $addressData['postcode'] ?? '36013';
        $city = $addressData['city'] ?? 'Montgomery';
        $street = $addressData['street'] ?? json_encode(["test street 1", "test street 2"]);

        return  <<<MUTATION
        address: {
            firstname: "{$firstName}"
            lastname: "{$lastName}"
            company: "{$company}"
            street: {$street}
            city: "{$city}"
            region: "{$region}"
            postcode: "{$postCode}"
            country_code: "{$countryCode}"
            telephone: "{$telephone}"
          }
MUTATION;
    }

    /**
     * Set the shipping method on the cart and return an available payment method
     *
     * @param array|null $shippingMethod
     * @param string $cartId
     * @return array
     */
    public function getAvailablePaymentMethods(?array $shippingMethod, string $cartId): array
    {
        $setShippingMethod = <<<MUTATION
mutation {
  setShippingMethodsOnCart(input:  {
    cart_id: "{$cartId}",
    shipping_methods: [
      {
         carrier_code: "{$shippingMethod['carrier_code']}"
         method_code: "{$shippingMethod['method_code']}"
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
MUTATION;
        $result = $this->makeRequest($setShippingMethod);
        $paymentMethod = $result['setShippingMethodsOnCart']['cart']['available_payment_methods'][0];
        return $paymentMethod;
    }

    /**
     * Set the payment method on the cart
     *
     * @param array $paymentMethod
     * @param string $cartId
     * @return array
     */
    public function setPaymentMethod(array $paymentMethod, string $cartId): array
    {
        $setPaymentMethod = <<<MUTATION
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$cartId}"
      payment_method: {
        code: "{$paymentMethod['code']}"
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
MUTATION;
        return $this->makeRequest($setPaymentMethod);
    }

    /**
     * Place the order
     *
     * @param string $cartId
     * @return array
     */
    public function doPlaceOrder(string $cartId): array
    {
        $placeOrder = <<<MUTATION
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
MUTATION;
        return $this->makeRequest($placeOrder);
    }
}
