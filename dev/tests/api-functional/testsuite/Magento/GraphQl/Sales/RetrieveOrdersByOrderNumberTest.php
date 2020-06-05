<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class RetrieveOrdersTest
 */
class RetrieveOrdersByOrderNumberTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var Order\Item */
    private $orderItem;

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->orderItem = $objectManager->get(Order\Item::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerOrdersSimpleProductQuery()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000002"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
      order_items{
        quantity_ordered
        product_sku
        product_name
        product_sale_price{currency value}
      }
      totals {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                   shipping_handling{total_amount{value currency}}
                    subtotal {
                        value
                        currency
                    }
                  taxes {amount {currency value} title rate}
                  discounts {
                        amount {
                            value
                            currency
                        }
                        label
                    }
                }
    }
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'][0];
        $expectedCount = count($response['customer']['orders']['items']);
        $this->assertCount($expectedCount, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('order_items', $customerOrderItemsInResponse);
        $this->assertNotEmpty($customerOrderItemsInResponse['order_items']);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', '100000002')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $items = $this->orderRepository->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $orderId = $item->getEntityId();
            $orderNumber = $item->getIncrementId();
            $this->assertEquals($orderId, $customerOrderItemsInResponse['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse['status']);
        }
        $expectedOrderItems =
            [ 'quantity_ordered'=> 2,
                'product_sku'=> 'simple',
                'product_name'=> 'Simple Product',
                'product_sale_price'=> ['currency'=> 'USD', 'value'=> 10]
            ];
        $actualOrderItemsFromResponse = $customerOrderItemsInResponse['order_items'][0];
        $this->assertEquals($expectedOrderItems, $actualOrderItemsFromResponse);
        //TODO: below function needs to be updated to reflect totals based on the order number used in each test
//        $this->assertTotals($response, $expectedCount);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetMatchingCustomerOrders()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{match:"100"}}){
    total_count
    page_info{
      total_pages
      current_page
      page_size
    }
    items
    {
      id
      number
      status
      order_date
      order_items{
        quantity_ordered
        product_sku
        product_name
        parent_product_sku
        product_sale_price{currency value}
      }

    }
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(4, $response['customer']['orders']['total_count']);
    }
    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetMatchingOrdersForLowerQueryLength()
    {
        $query =
            <<<QUERY
{
 customer
 {
  orders(filter:{number:{match:"00"}}){
   total_count
   page_info{
     total_pages
     current_page
     page_size
   }
   items
   {
     id
     number
     status
     order_date
     order_items{
       quantity_ordered
       product_sku
       product_name
     }
   }
  }
}
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid match filter. Minimum length is 3.');
        $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetMultipleCustomerOrdersQueryWithDefaultPagination()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{in:["100000005","100000006"]}}){
    total_count
    page_info{
      total_pages
      current_page
      page_size
    }
    items
    {
      id
      number
      status
      order_date
      order_items{
        quantity_ordered
        product_sku
        product_name
        parent_product_sku
        product_sale_price{currency value}
      }
            totals {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    shipping_handling{total_amount{value currency}}
                    subtotal {
                        value
                        currency
                    }
                    taxes {amount {currency value} title rate}
                    discounts {
                        amount {
                            value
                            currency
                        }
                        label
                    }
                }
    }
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(2, $response['customer']['orders']['total_count']);
        $this->assertArrayHasKey('page_info', $response['customer']['orders']);
        $pageInfo = $response['customer']['orders']['page_info'];
        $this->assertEquals(1, $pageInfo['current_page']);
        $this->assertEquals(20, $pageInfo['page_size']);
        $this->assertEquals(1, $pageInfo['total_pages']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'];
        $this->assertCount(2, $response['customer']['orders']['items']);

        $orderNumbers = ['100000005', '100000006'];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderNumbers, 'in')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $items = $this->orderRepository->getList($searchCriteria)->getItems();
        $key = 0;
        foreach ($items as $item) {
            $orderId = $item->getEntityId();
            $orderNumber = $item->getIncrementId();
            $this->assertEquals($orderId, $customerOrderItemsInResponse[$key]['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse[$key]['number']);
            $this->assertEquals('Complete', $customerOrderItemsInResponse[$key]['status']);
            //TODO: below function needs to be updated to reflect totals based on the order number being used in each test
//            $expectedCount = count($response['customer']['orders']['items']);
//            $this->assertTotals($customerOrderItemsInResponse[$key], $expectedCount);
            $key++;
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerOrdersUnauthorizedCustomer()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000001"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
    }
   }
 }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/two_customers.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/two_orders_for_two_diff_customers.php
     */
    public function testGetCustomerOrdersWithWrongCustomer()
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"100000001"}}){
    total_count
    items
    {
      id
      number
      status
      order_date
    }
   }
 }
}
QUERY;
        $currentEmail = 'customer_two@example.com';
        $currentPassword = 'password';
        $responseWithWrongCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['total_count']);
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['items']);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $responseWithCorrectCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['total_count']);
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['items']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/order_with_totals.php
     */
    public function testGetCustomerOrdersOnTotals()
    {
        $query =
            <<<QUERY
{
  customer {
    email
    orders(filter:{number:{eq:"100000001"}}) {
      total_count
      items {
        id
        number
        order_date
        status
        totals {
          base_grand_total {
            value
            currency
          }
          grand_total {
            value
            currency
          }
        shipping_handling{total_amount{value currency}}
          subtotal {
            value
            currency
          }
          taxes {amount{value currency} title rate}
          discounts {
            amount {
              value
              currency
            }
            label
          }
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
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $expectedCount = count($response["customer"]["orders"]["items"]);
        $this->assertTotals($response, $expectedCount);
    }

    /**
     * @param String $orderNumber
     * @dataProvider dataProviderIncorrectOrder
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerNonExistingOrderQuery(string $orderNumber)
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"{$orderNumber}"}}){
    items
    {
      number
      order_items{
        product_sku
      }
      totals {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    shipping_handling{total_amount{value currency}}
                    subtotal {
                        value
                        currency
                    }
                    taxes {amount{value currency} title rate}
                    discounts {
                        amount {
                            value
                            currency
                        }
                        label
                    }
                }
    }
    page_info {
        current_page
        page_size
        total_pages
    }
    total_count
   }
 }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertCount(0, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(0, $response['customer']['orders']['total_count']);
        $this->assertArrayHasKey('page_info', $response['customer']['orders']);
        $this->assertEquals(
            ['current_page' => 1, 'page_size' => 20, 'total_pages' => 0],
            $response['customer']['orders']['page_info']
        );
    }

    /**
     * @return array
     */
    public function dataProviderIncorrectOrder(): array
    {
        return [
            'correctFormatNonExistingOrder' => [
                '200000009',
            ],
            'alphaFormatNonExistingOrder' => [
                '200AA00B9',
            ],
            'longerFormatNonExistingOrder' => [
                'X0000-0033331',
            ],
        ];
    }

    /**
     * @param String $orderNumber
     * @param String $store
     * @param int $expectedCount
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @dataProvider dataProviderMultiStores
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/two_orders_with_order_items_two_storeviews.php
     */
    public function testGetCustomerOrdersTwoStoreViewQuery(string $orderNumber, string $store, int $expectedCount)
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"{$orderNumber}"}}){
    items
    {
      number
      order_items{
        product_sku
      }
      totals {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    shipping_handling {total_amount{value currency}}
                    subtotal {
                        value
                        currency
                    }
                    taxes {amount{value currency} title rate}
                    discounts {
                        amount {
                            value
                            currency
                        }
                        label
                    }
                }
    }
    page_info {
        current_page
        page_size
        total_pages
    }
    total_count
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
            array_merge($this->getCustomerAuthHeaders($currentEmail, $currentPassword), ['Store' => $store])
        );
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertCount($expectedCount, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals($expectedCount, (int)$response['customer']['orders']['total_count']);

        $this->assertTotals($response, $expectedCount);
    }

    /**
     * @return array
     */
    public function dataProviderMultiStores(): array
    {
        return [
            'firstStoreFirstOrder' => [
                '100000001', 'default', 1
            ],
            'secondStoreSecondOrder' => [
                '100000002', 'fixture_second_store', 1
            ],
            'firstStoreSecondOrder' => [
                '100000002', 'default', 0
            ],
            'secondStoreFirstOrder' => [
                '100000001', 'fixture_second_store', 0
            ],
        ];
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     *  Verify that the customer order has the tax information on shipping and totals
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     *
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testCustomerOrderWithDiscountsAndTaxesOnShipping()
    {

        $quantity = 2;

    }

    /**
     *  Verify that the customer order has the tax information on shipping and totals
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testCustomerOrderWithTaxesExcludedOnShipping()
    {
        $quantity = 2;
        $sku = 'simple1';
        $cartId = $this->createEmptyCart();
        $this->addProductToCart($cartId, $quantity, $sku);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $customerOrderResponse = $this->getCustomerOrderQuery($orderNumber);
        $customerOrderItem = $customerOrderResponse[0];
        $this->assertTotalsAndShippingWithExcludedTaxSetting($customerOrderItem);
        $this->deleteOrder();
    }

    /**
     * Assert order totals including shipping_handling and taxes
     *
     * @param array $customerOrderItem
     */
    private function assertTotalsAndShippingWithExcludedTaxSetting(array $customerOrderItem): void
    {
        $this->assertEquals(
            32.25,
            $customerOrderItem['totals']['base_grand_total']['value']
        );

        $this->assertEquals(
            32.25,
            $customerOrderItem['totals']['grand_total']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['totals']['subtotal']['value']
        );
        $this->assertEquals(
            2.25,
            $customerOrderItem['totals']['total_tax']['value']
        );

        $this->assertEquals(
            10,
            $customerOrderItem['totals']['total_shipping']['value']
        );
        $this->assertEquals(
            2.25,
            $customerOrderItem['totals']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['totals']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['totals']['taxes'][0]['rate']
        );
        $this->assertEquals(
            10.75,
            $customerOrderItem['totals']['shipping_handling']['amount_inc_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['totals']['shipping_handling']['amount_exc_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['totals']['shipping_handling']['total_amount']['value']
        );

        $this->assertEquals(
            2.25,
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['rate']
        );
    }
    /**
     *  Verify that the customer order has the tax information on shipping and totals
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_and_order_display_settings.php
     */
    public function testCustomerOrderWithTaxesOnShippingAndPrices()
    {
        $quantity = 2;
        $sku = 'simple1';
        $cartId = $this->createEmptyCart();
        $this->addProductToCart($cartId, $quantity, $sku);

        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);

        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);

        $orderNumber = $this->placeOrder($cartId);
        $customerOrderResponse = $this->getCustomerOrderQuery($orderNumber);
        $customerOrderItem = $customerOrderResponse[0];
        $this->assertTotalsAndShippingWithTaxes($customerOrderItem);
        $this->deleteOrder();
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
        $response = $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
    cart {items{quantity product {sku}}}}
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
        $response = $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
        $response = $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

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
        $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
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
        $response = $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        return $response['placeOrder']['order']['order_number'];
    }

    /**
     * Get customer order query
     *
     * @param string $orderNumber
     * @return array
     */
    private function getCustomerOrderQuery($orderNumber):array
    {
        $query =
            <<<QUERY
{
     customer {
       email
       orders(filter:{number:{eq:"{$orderNumber}"}}) {
         total_count
         items {
           id
           number
           order_date
           status
           order_items{product_name product_sku quantity_ordered}
           totals {
             base_grand_total{value currency}
             grand_total{value currency}
             total_tax{value}
             subtotal { value currency }
             taxes {amount{value currency} title rate}
             total_shipping{value}
             shipping_handling
             {
               amount_inc_tax{value}
               amount_exc_tax{value}
               total_amount{value}
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
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

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
     * Assert order totals including shipping_handling and taxes
     *
     * @param array $customerOrderItem
     */
    private function assertTotalsAndShippingWithTaxes(array $customerOrderItem): void
    {
        $this->assertEquals(
            31.43,
            $customerOrderItem['totals']['base_grand_total']['value']
        );

        $this->assertEquals(
            31.43,
            $customerOrderItem['totals']['grand_total']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['totals']['subtotal']['value']
        );
        $this->assertEquals(
            2.19,
            $customerOrderItem['totals']['total_tax']['value']
        );

        $this->assertEquals(
            9.24,
            $customerOrderItem['totals']['total_shipping']['value']
        );
        $this->assertEquals(
            2.19,
            $customerOrderItem['totals']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['totals']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['totals']['taxes'][0]['rate']
        );
        $this->assertEquals(
            9.93,
            $customerOrderItem['totals']['shipping_handling']['amount_inc_tax']['value']
        );
        $this->assertEquals(
            9.24,
            $customerOrderItem['totals']['shipping_handling']['amount_exc_tax']['value']
        );
        $this->assertEquals(
            9.24,
            $customerOrderItem['totals']['shipping_handling']['total_amount']['value']
        );

        $this->assertEquals(
            2.19,
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['totals']['shipping_handling']['taxes'][0]['rate']
        );
    }

    /**
     * Assert order totals
     *
     * @param array $response
     * @param int $expectedCount
     */
    private function assertTotals(array $response, int $expectedCount): void
    {
        if ($expectedCount === 0) {
            $this->assertEmpty($response['customer']['orders']['items']);
        } else {
            $this->assertEquals(
                100,
                $response['customer']['orders']['items'][0]['totals']['base_grand_total']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['totals']['base_grand_total']['currency']
            );
            $this->assertEquals(
                100,
                $response['customer']['orders']['items'][0]['totals']['grand_total']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['totals']['grand_total']['currency']
            );
            $this->assertEquals(
                110,
                $response['customer']['orders']['items'][0]['totals']['subtotal']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['totals']['subtotal']['currency']
            );
            $this->assertEquals(
                10,
                $response['customer']['orders']['items'][0]['totals']['shipping_handling']['total_amount']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['totals']['shipping_handling']['total_amount']['currency']
            );
            $this->assertEquals(
                5,
                $response['customer']['orders']['items'][0]['totals']['taxes'][0]['amount']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['totals']['taxes'][0]['amount']['currency']
            );
        }

    }
}
