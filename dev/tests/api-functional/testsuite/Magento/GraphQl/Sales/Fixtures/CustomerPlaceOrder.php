<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales\Fixtures;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQl\Client;

class CustomerPlaceOrder
{
    /**
     * @var Client
     */
    private $gqlClient;

    /**
     * @var CustomerTokenServiceInterface
     */
    private $tokenService;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var string
     */
    private $authHeader;

    /**
     * @var string
     */
    private $cartId;

    /**
     * @var array
     */
    private $customerLogin;

    /**
     * @param Client $gqlClient
     * @param CustomerTokenServiceInterface $tokenService
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Client $gqlClient,
        CustomerTokenServiceInterface $tokenService,
        ProductRepositoryInterface $productRepository
    ) {
        $this->gqlClient = $gqlClient;
        $this->tokenService = $tokenService;
        $this->productRepository = $productRepository;
    }

    /**
     * Place order for a bundled product
     *
     * @param array $customerLogin
     * @param array $productData
     * @param array|null $addressData
     * @return array
     */
    public function placeOrderWithBundleProduct(
        array $customerLogin,
        array $productData,
        ?array $addressData = null
    ): array {
        $this->customerLogin = $customerLogin;
        $this->createCustomerCart();
        $this->addBundleProduct($productData);
        $this->setBillingAddress($addressData);
        $shippingMethod = $this->setShippingAddress($addressData);
        $paymentMethod = $this->setShippingMethod($shippingMethod);
        $this->setPaymentMethod($paymentMethod);
        return $this->doPlaceOrder();
    }

    /**
     * Make GraphQl POST request
     *
     * @param string $query
     * @param array $additionalHeaders
     * @return array
     */
    private function makeRequest(string $query, array $additionalHeaders = []): array
    {
        $headers = array_merge([$this->getAuthHeader()], $additionalHeaders);
        return $this->gqlClient->post($query, [], '', $headers);
    }

    /**
     * Get header for authenticated requests
     *
     * @return string
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getAuthHeader(): string
    {
        if (empty($this->authHeader)) {
            $customerToken = $this->tokenService
                ->createCustomerAccessToken($this->customerLogin['email'], $this->customerLogin['password']);
            $this->authHeader = "Authorization: Bearer {$customerToken}";
        }
        return $this->authHeader;
    }

    /**
     * Get cart id
     *
     * @return string
     */
    private function getCartId(): string
    {
        if (empty($this->cartId)) {
            $this->cartId = $this->createCustomerCart();
        }
        return $this->cartId;
    }

    /**
     * Create empty cart for the customer
     *
     * @return array
     */
    private function createCustomerCart(): string
    {
        //Create empty cart
        $createEmptyCart = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
        $result = $this->makeRequest($createEmptyCart);
        return $result['createEmptyCart'];
    }

    /**
     * Add a bundle product to the cart
     *
     * @param array $productData
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addBundleProduct(array $productData)
    {
        $productSku = $productData['sku'];
        $qty = $productData['quantity'] ?? 1;
        /** @var Product $bundleProduct */
        $bundleProduct = $this->productRepository->get($productSku);
        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $bundleProduct->getTypeInstance();
        $optionId1 = (int)$typeInstance->getOptionsCollection($bundleProduct)->getFirstItem()->getId();
        $optionId2 = (int)$typeInstance->getOptionsCollection($bundleProduct)->getLastItem()->getId();
        $selectionId1 = (int)$typeInstance->getSelectionsCollection([$optionId1], $bundleProduct)
            ->getFirstItem()
            ->getSelectionId();
        $selectionId2 = (int)$typeInstance->getSelectionsCollection([$optionId2], $bundleProduct)
            ->getLastItem()
            ->getSelectionId();

        $addProduct = <<<QUERY
mutation {
  addBundleProductsToCart(input:{
    cart_id:"{$this->getCartId()}"
    cart_items:[
      {
        data:{
          sku:"{$productSku}"
          quantity:{$qty}
        }
        bundle_options:[
          {
            id:{$optionId1}
            quantity:1
            value:["{$selectionId1}"]
          }
          {
            id:$optionId2
            quantity:2
            value:["{$selectionId2}"]
          }
        ]
      }
    ]
  }) {
    cart {
      items {quantity product {sku}}
      }
    }
}
QUERY;
        return $this->makeRequest($addProduct);
    }

    /**
     * Set the billing address on the cart
     *
     * @param array $addressData
     * @return array
     */
    private function setBillingAddress(?array $addressData = null): array
    {
        $telephone = $addressData['telephone'] ?? '5123456677';
            $setBillingAddress = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$this->getCartId()}"
      billing_address: {
         address: {
          firstname: "John"
          lastname: "Smith"
          company: "Test company"
          street: ["test street 1", "test street 2"]
          city: "Texas City"
          postcode: "78717"
          telephone: "{$telephone}"
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
        return $this->makeRequest($setBillingAddress);
    }

    /**
     * Set the shipping address on the cart and return an available shipping method
     *
     * @param array|null $addressData
     * @return array
     */
    private function setShippingAddress(?array $addressData): array
    {
        $telephone = $addressData['telephone'] ?? '5123456677';
        $setShippingAddress = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "{$this->getCartId()}"
      shipping_addresses: [
        {
          address: {
            firstname: "test shipFirst"
            lastname: "test shipLast"
            company: "test company"
            street: ["test street 1", "test street 2"]
            city: "Montgomery"
            region: "AL"
            postcode: "36013"
            country_code: "US"
            telephone: "{$telephone}"
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
          amount {value}
        }
      }
    }
  }
}
QUERY;
        $result = $this->makeRequest($setShippingAddress);
        $shippingMethod = $result['setShippingAddressesOnCart']
        ['cart']['shipping_addresses'][0]['available_shipping_methods'][0];
        return $shippingMethod;
    }

    /**
     * Set the shipping method on the cart and return an available payment method
     *
     * @param array|null $shippingMethod
     * @return array
     */
    private function setShippingMethod(?array $shippingMethod): array
    {
        $setShippingMethod = <<<QUERY
mutation {
  setShippingMethodsOnCart(input:  {
    cart_id: "{$this->getCartId()}",
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
QUERY;
        $result = $this->makeRequest($setShippingMethod);
        $paymentMethod = $result['setShippingMethodsOnCart']['cart']['available_payment_methods'][0];
        return $paymentMethod;
    }

    /**
     * Set the payment method on the cart
     *
     * @param array $paymentMethod
     * @return array
     */
    private function setPaymentMethod(array $paymentMethod): array
    {
        $setPaymentMethod = <<<QUERY
mutation {
  setPaymentMethodOnCart(
    input: {
      cart_id: "{$this->getCartId()}"
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
QUERY;
        return $this->makeRequest($setPaymentMethod);
    }

    /**
     * Place the order
     *
     * @return array
     */
    private function doPlaceOrder(): array
    {
        $placeOrder = <<<QUERY
mutation {
  placeOrder(
    input: {
      cart_id: "{$this->getCartId()}"
    }
  ) {
    order {
      order_number
    }
  }
}
QUERY;
        return $this->makeRequest($placeOrder);
    }
}
