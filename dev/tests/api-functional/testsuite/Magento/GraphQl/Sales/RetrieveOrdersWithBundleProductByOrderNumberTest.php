<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Bundle\Model\Selection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for orders with bundle product
 */
class RetrieveOrdersWithBundleProductByOrderNumberTest extends GraphQlAbstract
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var GetCustomerAuthenticationHeader */
    private $customerAuthenticationHeader;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Test customer order details with bundle product with child items
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     */
    public function testGetCustomerOrderBundleProduct()
    {
        $qty = 1;
        $bundleSku = 'bundle-product-two-dropdown-options';
        $optionsAndSelectionData = $this->getBundleOptionAndSelectionData($bundleSku);

        $cartId = $this->createEmptyCart();
        $this->addBundleProductQuery($cartId, $qty, $bundleSku, $optionsAndSelectionData);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $customerOrderResponse = $this->getCustomerOrderQueryBundleProduct($orderNumber);

        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals("Pending", $customerOrderItems['status']);
        $bundledItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            'bundle-product-two-dropdown-options-simple1-simple2',
            $bundledItemInTheOrder['product_sku']
        );
        $priceOfBundledItemInOrder = $bundledItemInTheOrder['product_sale_price']['value'];
        $this->assertEquals(15, $priceOfBundledItemInOrder);
        $this->assertArrayHasKey('bundle_options', $bundledItemInTheOrder);
        $bundleOptionsFromResponse = $bundledItemInTheOrder['bundle_options'];
        $this->assertNotEmpty($bundleOptionsFromResponse);
        $this->assertEquals(2, count($bundleOptionsFromResponse));
        $expectedBundleOptions =
            [
              [  '__typename' => 'ItemSelectedBundleOption',
                  'label' => 'Drop Down Option 1',
                  'values' => [
                      [
                        'product_sku' => 'simple1',
                        'product_name' => 'Simple Product1',
                        'quantity'=> 1,
                          'price' => [
                            'value' => 1,
                            'currency' => 'USD'
                          ]
                      ]
                ]
              ],
                [  '__typename' => 'ItemSelectedBundleOption',
                    'label' => 'Drop Down Option 2',
                    'values' => [
                        [
                            'product_sku' => 'simple2',
                            'product_name' => 'Simple Product2',
                            'quantity'=> 2,
                            'price' => [
                                'value' => 2,
                                'currency' => 'USD'
                            ]
                        ]
                    ]
                ],
            ];
        $this->assertEquals($expectedBundleOptions, $bundleOptionsFromResponse);
        $this->deleteOrder();
    }

    /**
     * Test customer order details with bundle products
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testGetCustomerOrderBundleProductWithTaxesAndDiscounts()
    {
        $qty = 4;
        $bundleSku = 'bundle-product-two-dropdown-options';
        $optionsAndSelectionData = $this->getBundleOptionAndSelectionData($bundleSku);

        $cartId = $this->createEmptyCart();
        $this->addBundleProductQuery($cartId, $qty, $bundleSku, $optionsAndSelectionData);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $customerOrderResponse = $this->getCustomerOrderQueryBundleProduct($orderNumber);

        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals("Pending", $customerOrderItems['status']);

        $bundledItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            'bundle-product-two-dropdown-options-simple1-simple2',
            $bundledItemInTheOrder['product_sku']
        );
        $this->assertArrayHasKey('bundle_options', $bundledItemInTheOrder);
        $childItemsInTheOrder = $bundledItemInTheOrder['bundle_options'];
        $this->assertNotEmpty($childItemsInTheOrder);
        $this->assertCount(2, $childItemsInTheOrder);
        $this->assertEquals('Drop Down Option 1', $childItemsInTheOrder[0]['label']);
        $this->assertEquals('Drop Down Option 2', $childItemsInTheOrder[1]['label']);

        $this->assertEquals('simple1', $childItemsInTheOrder[0]['values'][0]['product_sku']);
        $this->assertEquals('simple2', $childItemsInTheOrder[1]['values'][0]['product_sku']);
        $this->assertTotalsOnBundleProductWithTaxesAndDiscounts($customerOrderItems['total']);
        $this->deleteOrder();
    }

    /**
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsOnBundleProductWithTaxesAndDiscounts(array $customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(5.4, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 77.4, 'currency' =>'USD'],
            'grand_total' => ['value' => 77.4, 'currency' =>'USD'],
            'subtotal' => ['value' => 60, 'currency' =>'USD'],
            'total_tax' => ['value' => 5.4, 'currency' =>'USD'],
            'total_shipping' => ['value' => 20, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 21.5],
                'amount_excluding_tax' => ['value' => 20],
                'total_amount' => ['value' => 20],
                'discounts' => [
                    0 => ['amount'=>['value'=>2],
                        'label' => 'Discount'
                    ]
                ],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 1.35],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ]
                ]
            ],
            'discounts' => [
                0 => ['amount' => [ 'value' => -8, 'currency' =>'USD'],
                    'label' => 'Discount'
                ]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        return $response['createEmptyCart'];
    }

    /**
     *  Add bundle product to cart with Graphql query
     *
     * @param string $cartId
     * @param float $qty
     * @param string $sku
     * @param array $optionsAndSelectionData
     * @throws AuthenticationException
     */
    public function addBundleProductQuery(
        string $cartId,
        float $qty,
        string $sku,
        array $optionsAndSelectionData
    ) {
        $query = <<<QUERY
mutation {
  addBundleProductsToCart(input:{
    cart_id:"{$cartId}"
    cart_items:[
      {
        data:{
          sku:"{$sku}"
          quantity:$qty
        }
        bundle_options:[
          {
            id:$optionsAndSelectionData[0]
            quantity:1
            value:["{$optionsAndSelectionData[1]}"]
          }
          {
            id:$optionsAndSelectionData[2]
            quantity:2
            value:["{$optionsAndSelectionData[3]}"]
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertArrayHasKey('cart', $response['addBundleProductsToCart']);
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $shippingAddress = current($response['setShippingAddressesOnCart']['cart']['shipping_addresses']);
        $availableShippingMethod = current($shippingAddress['available_shipping_methods']);
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        $availablePaymentMethod = current($response['setShippingMethodsOnCart']['cart']['available_payment_methods']);
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
    cart {selected_payment_method {code}}
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
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
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        return $response['placeOrder']['order']['order_number'];
    }

    /**
     * Get customer order query for bundle order items
     *
     * @param $orderNumber
     * @return mixed
     * @throws AuthenticationException
     */
    private function getCustomerOrderQueryBundleProduct($orderNumber)
    {
        $query =
            <<<QUERY
{
     customer {
       orders(filter:{number:{eq:"{$orderNumber}"}}) {
         total_count
         items {
          id
           number
           order_date
           status
           items{
            __typename
            product_sku
            product_name
            product_url_key
            product_sale_price{value}
            quantity_ordered
            discounts{amount{value} label}
            ... on BundleOrderItem{
              bundle_options{
                __typename
                label
                values {
                  product_sku
                  product_name
                  quantity
                  price {
                    value
                    currency
                  }
                }
              }
          }
         }
           total {
             base_grand_total{value currency}
             grand_total{value currency}
             subtotal {value currency }
             total_tax{value currency}
             taxes {amount{value currency} title rate}
             total_shipping{value currency}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value}
               discounts{amount{value} label}
               taxes {amount{value} title rate}
             }
             discounts {amount{value currency} label}
           }
         }
       }
     }
   }
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'];
        return $customerOrderItemsInResponse;
    }

    /**
     * @return void
     */
    private function deleteOrder(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(Collection::class);
        //$orderCollection = $this->orderCollectionFactory->create();
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @param string $bundleSku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBundleOptionAndSelectionData($bundleSku): array
    {
        /** @var Product $bundleProduct */
        $bundleProduct = $this->productRepository->get($bundleSku);
        /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
        $typeInstance = $bundleProduct->getTypeInstance();
        $optionsAndSelections = [];
        /** @var $option \Magento\Bundle\Model\Option */
        $option1 = $typeInstance->getOptionsCollection($bundleProduct)->getFirstItem();
        $option2 = $typeInstance->getOptionsCollection($bundleProduct)->getLastItem();
        $optionId1 =(int) $option1->getId();
        $optionId2 =(int) $option2->getId();
        /** @var Selection $selection */
        $selection1 = $typeInstance->getSelectionsCollection([$option1->getId()], $bundleProduct)->getFirstItem();
        $selectionId1 = (int)$selection1->getSelectionId();
        $selection2 = $typeInstance->getSelectionsCollection([$option2->getId()], $bundleProduct)->getLastItem();
        $selectionId2 = (int)$selection2->getSelectionId();
        array_push($optionsAndSelections, $optionId1, $selectionId1, $optionId2, $selectionId2);
        return $optionsAndSelections;
    }
}
