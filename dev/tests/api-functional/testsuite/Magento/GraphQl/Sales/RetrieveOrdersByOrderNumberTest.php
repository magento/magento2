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
 * Class RetrieveOrdersTest
 */
class RetrieveOrdersByOrderNumberTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
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
      items{
        quantity_ordered
        product_sku
        product_name
        product_sale_price{currency value}
      }
      total {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    subtotal {
                        value
                        currency
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
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'][0];
        $this->assertArrayHasKey('items', $customerOrderItemsInResponse);
        $this->assertNotEmpty($customerOrderItemsInResponse['items']);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', '100000002')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        foreach ($orders as $order) {
            $orderNumber = $order->getIncrementId();
            $this->assertNotEmpty($customerOrderItemsInResponse['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse['status']);
        }
        $expectedOrderItems =
            [ 'quantity_ordered'=> 2,
                'product_sku'=> 'simple',
                'product_name'=> 'Simple Product',
                'product_sale_price'=> ['currency'=> 'USD', 'value'=> 10]
            ];
        $actualOrderItemsFromResponse = $customerOrderItemsInResponse['items'][0];
        $this->assertEquals($expectedOrderItems, $actualOrderItemsFromResponse);
        $actualOrderTotalFromResponse = $response['customer']['orders']['items'][0]['total'];
        $expectedOrderTotal =
            [
                'base_grand_total' => ['value'=> 120,'currency' =>'USD'],
                'grand_total' => ['value'=> 120,'currency' =>'USD'],
                'subtotal' => ['value'=> 120,'currency' =>'USD']
            ];
        $this->assertEquals($expectedOrderTotal, $actualOrderTotalFromResponse, 'Totals do not match');
    }

    /**
     *  Verify the customer order with tax, discount with shipping tax class set for calculation setting
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testCustomerOrdersSimpleProductWithTaxesAndDiscounts()
    {
        $quantity = 4;
        $sku = 'simple1';
        $cartId = $this->createEmptyCart();
        $this->addProductToCart($cartId, $quantity, $sku);
        $this->setBillingAddress($cartId);
        $shippingMethod = $this->setShippingAddress($cartId);
        $paymentMethod = $this->setShippingMethod($cartId, $shippingMethod);
        $this->setPaymentMethod($cartId, $paymentMethod);
        $orderNumber = $this->placeOrder($cartId);
        $customerOrderResponse = $this->getCustomerOrderQuery($orderNumber);
        // Asserting discounts on order item level
        $this->assertEquals(
            4,
            $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['value']
        );
        $this->assertEquals(
            'USD',
            $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['currency']
        );
        $this->assertEquals(
            'null',
            $customerOrderResponse[0]['items'][0]['discounts'][0]['label']
        );
        $customerOrderItem = $customerOrderResponse[0];
        $this->assertTotalsWithTaxesAndDiscountsOnShippingAndTotal($customerOrderItem);
        $this->deleteOrder();
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
                            'value' => 1
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
                                'value' => 2
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

        $this->assertTotalsOnBundleProductWithTaxesAndDiscounts($customerOrderItems);
        $this->deleteOrder();
    }

    /**
     * Assert order totals including shipping_handling and taxes
     *
     * @param array $customerOrderItem
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function assertTotalsOnBundleProductWithTaxesAndDiscounts(array $customerOrderItem): void
    {
        $this->assertEquals(
            77.4,
            $customerOrderItem['total']['base_grand_total']['value']
        );

        $this->assertEquals(
            77.4,
            $customerOrderItem['total']['grand_total']['value']
        );
        $this->assertEquals(
            60,
            $customerOrderItem['total']['subtotal']['value']
        );
        $this->assertEquals(
            5.4,
            $customerOrderItem['total']['total_tax']['value']
        );

        $this->assertEquals(
            20,
            $customerOrderItem['total']['total_shipping']['value']
        );
        $this->assertCount(2, $customerOrderItem['total']['taxes']);
        $expectedProductAndShippingTaxes = [4.05, 1.35];

        $totalTaxes = [];
        foreach ($customerOrderItem['total']['taxes'] as $totalTaxFromResponse) {
            array_push($totalTaxes, $totalTaxFromResponse['amount']['value']);
        }
        foreach ($totalTaxes as $value) {
            $this->assertTrue(in_array($value, $expectedProductAndShippingTaxes));
        }
        $this->assertEquals(
            'USD',
            $customerOrderItem['total']['taxes'][0]['amount']['currency']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['taxes'][0]['rate']
        );
        $this->assertEquals(
            'USD',
            $customerOrderItem['total']['taxes'][1]['amount']['currency']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['taxes'][1]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['taxes'][1]['rate']
        );
        $this->assertEquals(
            21.5,
            $customerOrderItem['total']['shipping_handling']['amount_including_tax']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['shipping_handling']['amount_excluding_tax']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['shipping_handling']['total_amount']['value']
        );

        $this->assertEquals(
            1.35,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['rate']
        );
        $this->assertEquals(
            2,
            $customerOrderItem['total']['shipping_handling']['discounts'][0]['amount']['value']
        );
        $this->assertEquals(
            'null',
            $customerOrderItem['total']['shipping_handling']['discounts'][0]['label']
        );
        $this->assertEquals(
            -8,
            $customerOrderItem['total']['discounts'][0]['amount']['value']
        );
        $this->assertEquals(
            'USD',
            $customerOrderItem['total']['discounts'][0]['amount']['currency']
        );
        $this->assertEquals(
            'null',
            $customerOrderItem['total']['discounts'][0]['label']
        );
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
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
      items{
        quantity_ordered
        product_sku
        product_name
        product_type
        product_sale_price{currency value}
        product_url_key
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
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(6, $response['customer']['orders']['total_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
     */
    public function testGetMatchingOrdersForLowerQueryLength()
    {
        $query =
            <<<QUERY
{
 customer
 {
  orders(filter:{number:{match:"0"}}){
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
     items{
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
        //character length should not trigger an exception
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals(6, $response['customer']['orders']['total_count']);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetMultipleCustomerOrdersQueryWithDefaultPagination()
    {
        $orderNumbers = ['100000007', '100000008'];
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{in:["{$orderNumbers[0]}","{$orderNumbers[1]}"]}}){
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
      items{
        quantity_ordered
        product_sku
        product_name
        product_type
        product_sale_price{currency value}
      }
       total {
                base_grand_total {value currency}
                 grand_total {value currency}
                    subtotal {value currency}
                    total_shipping{value}
                    total_tax{value currency}
                    taxes {amount {currency value} title rate}
                   total_shipping{value}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value}
               taxes {amount{value} title rate}
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
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

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

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderNumbers, 'in')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $items */
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        $key = 0;
        foreach ($orders as $order) {
            $orderNumber = $order->getIncrementId();
            $this->assertNotEmpty($customerOrderItemsInResponse[$key]['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse[$key]['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse[$key]['status']);
            $this->assertEquals(
                4,
                $customerOrderItemsInResponse[$key]['total']['shipping_handling']['total_amount']['value']
            );
            $this->assertEquals(
                0,
                $customerOrderItemsInResponse[$key]['total']['shipping_handling']['taxes'][0]['amount']['value']
            );
            $this->assertEquals(
                5,
                $customerOrderItemsInResponse[$key]['total']['total_shipping']['value']
            );
            $this->assertEquals(
                5,
                $customerOrderItemsInResponse[$key]['total']['total_tax']['value']
            );

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
     * @magentoApiDataFixture Magento/Sales/_files/two_orders_for_two_diff_customers.php
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
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['total_count']);
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['items']);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $responseWithCorrectCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['total_count']);
        $this->assertNotEmpty($responseWithCorrectCustomer['customer']['orders']['items']);
    }

    /**
     * @param String $orderNumber
     * @throws AuthenticationException
     * @dataProvider dataProviderIncorrectOrder
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
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
      items{
        product_sku
      }
      total {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                     total_shipping{value}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value}
               taxes {amount{value} title rate}
             }
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
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
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
     * @throws AuthenticationException
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
      items{
        product_sku
      }
      total {
                    base_grand_total {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    total_shipping{value}
                    shipping_handling
                    {
                     amount_including_tax{value}
                     amount_excluding_tax{value}
                     total_amount{value currency}
                     taxes {amount{value} title rate}
                    }
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
            array_merge(
                $this->customerAuthenticationHeader->execute(
                    $currentEmail,
                    $currentPassword
                ),
                ['Store' => $store]
            )
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
     * Assert order totals including shipping_handling and taxes
     *
     * @param array $customerOrderItem
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function assertTotalsWithTaxesAndDiscountsOnShippingAndTotal(array $customerOrderItem): void
    {
        $this->assertEquals(
            58.05,
            $customerOrderItem['total']['base_grand_total']['value']
        );

        $this->assertEquals(
            58.05,
            $customerOrderItem['total']['grand_total']['value']
        );
        $this->assertEquals(
            40,
            $customerOrderItem['total']['subtotal']['value']
        );
        $this->assertEquals(
            4.05,
            $customerOrderItem['total']['total_tax']['value']
        );
        $this->assertEquals(
            -6,
            $customerOrderItem['total']['discounts'][0]['amount']['value']
        );
        $this->assertEquals(
            'null',
            $customerOrderItem['total']['discounts'][0]['label']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['total_shipping']['value']
        );
        $this->assertCount(2, $customerOrderItem['total']['taxes']);
        $expectedProductAndShippingTaxes = [2.7, 1.35];

        $totalTaxes = [];
        foreach ($customerOrderItem['total']['taxes'] as $totalTaxFromResponse) {
            array_push($totalTaxes, $totalTaxFromResponse['amount']['value']);
        }
        foreach ($totalTaxes as $value) {
            $this->assertTrue(in_array($value, $expectedProductAndShippingTaxes));
        }

        $this->assertEquals(
            21.5,
            $customerOrderItem['total']['shipping_handling']['amount_including_tax']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['shipping_handling']['amount_excluding_tax']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['shipping_handling']['total_amount']['value']
        );

        $this->assertEquals(
            1.35,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['rate']
        );
        $this->assertEquals(
            2,
            $customerOrderItem['total']['shipping_handling']['discounts'][0]['amount']['value']
        );

        $this->assertEquals(
            'null',
            $customerOrderItem['total']['shipping_handling']['discounts'][0]['label']
        );
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
            $customerOrderItem['total']['base_grand_total']['value']
        );
        $this->assertEquals(
            32.25,
            $customerOrderItem['total']['grand_total']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['subtotal']['value']
        );
        $this->assertEquals(
            2.25,
            $customerOrderItem['total']['total_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['total']['total_shipping']['value']
        );
        $expectedProductAndShippingTaxes = [1.5, 0.75];

        $totalTaxes = [];
        foreach ($customerOrderItem['total']['taxes'] as $totalTaxFromResponse) {
            array_push($totalTaxes, $totalTaxFromResponse['amount']['value']);
        }
        foreach ($totalTaxes as $value) {
            $this->assertTrue(in_array($value, $expectedProductAndShippingTaxes));
        }

        $this->assertEquals(
            10.75,
            $customerOrderItem['total']['shipping_handling']['amount_including_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['total']['shipping_handling']['amount_excluding_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['total']['shipping_handling']['total_amount']['value']
        );
        $this->assertCount(1, $customerOrderItem['total']['shipping_handling']['taxes'], 'Count is incorrect');

        $this->assertEquals(
            0.75,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['rate']
        );
    }
    /**
     *  Verify that the customer order has the tax information on shipping and totals
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_and_order_display_settings.php
     */
    public function testCustomerOrderWithTaxesIncludedOnShippingAndTotals()
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
        $response = $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
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
        $this->graphQlMutation(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
    }

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
           items{product_name product_sku quantity_ordered discounts {amount{value currency} label}}
           total {
             base_grand_total{value currency}
             grand_total{value currency}
             total_tax{value}
             subtotal { value currency }
             taxes {amount{value currency} title rate}
             discounts {amount{value currency} label}
             total_shipping{value}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value currency}
               taxes {amount{value} title rate}
               discounts {amount{value currency} label}
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
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'];
        return $customerOrderItemsInResponse;
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
                  }
                }
              }
          }
         }
           total {
             base_grand_total{value currency}
             grand_total{value currency}
             total_tax{value}
             subtotal { value currency }
             taxes {amount{value currency} title rate}
             total_shipping{value}
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
     * Assert order totals including shipping_handling and taxes
     *
     * @param array $customerOrderItem
     */
    private function assertTotalsAndShippingWithTaxes(array $customerOrderItem): void
    {
        $this->assertEquals(
            32.25,
            $customerOrderItem['total']['base_grand_total']['value']
        );

        $this->assertEquals(
            32.25,
            $customerOrderItem['total']['grand_total']['value']
        );
        $this->assertEquals(
            20,
            $customerOrderItem['total']['subtotal']['value']
        );
        $this->assertEquals(
            2.25,
            $customerOrderItem['total']['total_tax']['value']
        );

        $this->assertEquals(
            10,
            $customerOrderItem['total']['total_shipping']['value']
        );
        $this->assertEquals(
            0.75,
            $customerOrderItem['total']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['taxes'][0]['rate']
        );
        $this->assertEquals(
            10.75,
            $customerOrderItem['total']['shipping_handling']['amount_including_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['total']['shipping_handling']['amount_excluding_tax']['value']
        );
        $this->assertEquals(
            10,
            $customerOrderItem['total']['shipping_handling']['total_amount']['value']
        );

        $this->assertEquals(
            0.75,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['amount']['value']
        );
        $this->assertEquals(
            'US-TEST-*-Rate-1',
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['title']
        );
        $this->assertEquals(
            7.5,
            $customerOrderItem['total']['shipping_handling']['taxes'][0]['rate']
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
                $response['customer']['orders']['items'][0]['total']['base_grand_total']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['base_grand_total']['currency']
            );
            $this->assertEquals(
                100,
                $response['customer']['orders']['items'][0]['total']['grand_total']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['grand_total']['currency']
            );
            $this->assertEquals(
                110,
                $response['customer']['orders']['items'][0]['total']['subtotal']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['subtotal']['currency']
            );
            $this->assertEquals(
                10,
                $response['customer']['orders']['items'][0]['total']['shipping_handling']['total_amount']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['shipping_handling']['total_amount']['currency']
            );
            $this->assertEquals(
                0,
                $response['customer']['orders']['items'][0]['total']['taxes'][0]['amount']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['taxes'][0]['amount']['currency']
            );
        }
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
