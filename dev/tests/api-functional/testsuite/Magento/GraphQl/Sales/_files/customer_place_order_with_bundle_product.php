<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQl\Client;

//Customer and product should be created using @magentoApiDataFixture annotations in test

$customerAuthHeader = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$bundleSku = 'bundle-product-two-dropdown-options';
$auth = $customerAuthHeader->execute('customer@example.com', 'password');
$headers = [
    sprintf('%s: %s', array_keys($auth)[0], $auth[array_keys($auth)[0]])
];

/** @var Client $graphQlClient */
$graphQlClient = Bootstrap::getObjectManager()->get(Client::class);

//Create empty cart
$createEmptyCart = <<<QUERY
mutation {
  createEmptyCart
}
QUERY;
$result = $graphQlClient->post($createEmptyCart, [], '', $headers);
$cartId = $result['createEmptyCart'];

//Add bundle product
$qty = 2;
/** @var Product $bundleProduct */
$bundleProduct = $productRepository->get($bundleSku);
/** @var $typeInstance \Magento\Bundle\Model\Product\Type */
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
    cart_id:"{$cartId}"
    cart_items:[
      {
        data:{
          sku:"{$bundleSku}"
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
$result = $graphQlClient->post($addProduct, [], '', $headers);

//Set billing Address
$setBillingAddress = <<<QUERY
mutation {
  setBillingAddressOnCart(
    input: {
      cart_id: "{$cartId}"
      billing_address: {
         address: {
          firstname: "John"
          lastname: "Smith"
          company: "Test company"
          street: ["test street 1", "test street 2"]
          city: "Texas City"
          postcode: "78717"
          telephone: "5123456677"
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
$result = $graphQlClient->post($setBillingAddress, [], '', $headers);

//Set shipping address
$setShippingAddress = <<<QUERY
mutation {
  setShippingAddressesOnCart(
    input: {
      cart_id: "$cartId"
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
            telephone: "3347665522"
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
$result = $graphQlClient->post($setShippingAddress, [], '', $headers);
$shippingMethod = $result['setShippingAddressesOnCart']['cart']['shipping_addresses'][0]['available_shipping_methods'][0];

//Set shipping method
$setShippingMethod = <<<QUERY
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
QUERY;
$result = $graphQlClient->post($setShippingMethod, [], '', $headers);
$paymentMethod = $result['setShippingMethodsOnCart']['cart']['available_payment_methods'][0];

//Set payment method
$setPaymentMethod = <<<QUERY
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
QUERY;
$result = $graphQlClient->post($setPaymentMethod, [], '', $headers);

//Place order
$placeOrder = <<<QUERY
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
$result = $graphQlClient->post($placeOrder, [], '', $headers);


    /**
     * @return void
     */
//    private function deleteOrder(): void
//    {
//        /** @var \Magento\Framework\Registry $registry */
//        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
//        $registry->unregister('isSecureArea');
//        $registry->register('isSecureArea', true);
//
//        /** @var $order \Magento\Sales\Model\Order */
//        $orderCollection = Bootstrap::getObjectManager()->create(Collection::class);
//        //$orderCollection = $this->orderCollectionFactory->create();
//        foreach ($orderCollection as $order) {
//            $this->orderRepository->delete($order);
//        }
//        $registry->unregister('isSecureArea');
//        $registry->register('isSecureArea', false);
//    }
