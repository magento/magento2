<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Registry;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddress;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddress;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethod;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrder;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\TestFramework\Fixture\Config;

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

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
     */
    public function testGetCustomerOrdersSimpleProductQuery()
    {
        $orderNumber = '100000002';
        $response = $this->getCustomerOrderQueryOnSimpleProducts($orderNumber);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'][0];
        $this->assertArrayHasKey('items', $customerOrderItemsInResponse);
        $this->assertNotEmpty($customerOrderItemsInResponse['items']);
        $this->assertNotEmpty($response["customer"]["orders"]["items"][0]["billing_address"]);
        $this->assertNotEmpty($response["customer"]["orders"]["items"][0]["shipping_address"]);
        $this->assertNotEmpty($response["customer"]["orders"]["items"][0]["payment_methods"]);

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', '100000002')
            ->create();
        /** @var \Magento\Sales\Api\Data\OrderInterface[] $orders */
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        foreach ($orders as $order) {
            $orderNumber = $order->getIncrementId();
            $this->assertNotEmpty($customerOrderItemsInResponse['id']);
            $this->assertEquals($orderNumber, $customerOrderItemsInResponse['number']);
            $this->assertEquals('Processing', $customerOrderItemsInResponse['status']);
        }
        $expectedOrderItems = [
            'quantity_ordered'=> 2,
            'product_sku'=> 'simple',
            'product_name'=> 'Simple Product',
            'product_sale_price'=> ['currency'=> 'USD', 'value'=> 10]
        ];
        $actualOrderItemsFromResponse = $customerOrderItemsInResponse['items'][0];
        $this->assertEquals($expectedOrderItems, $actualOrderItemsFromResponse);
        $actualOrderTotalFromResponse = $response['customer']['orders']['items'][0]['total'];
        $expectedOrderTotal = [
            'base_grand_total' => ['value'=> 120,'currency' =>'USD'],
            'grand_total' => ['value'=> 120,'currency' =>'USD'],
            'subtotal' => ['value'=> 120,'currency' =>'USD']
        ];
        $this->assertEquals($expectedOrderTotal, $actualOrderTotalFromResponse, 'Totals do not match');
    }

    #[
        Config(TaxConfig::XML_PATH_DISPLAY_SALES_PRICE, TaxConfig::DISPLAY_TYPE_INCLUDING_TAX),
    ]
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
        $billingAssertionMap = [
            'firstname' => 'John',
            'lastname' => 'Smith',
            'city' => 'Texas City',
            'company' => 'Test company',
            'country_code' => 'US',
            'postcode' => '78717',
            'region' => 'Texas',
            'region_id' => '57',
            'street' => [
                0 => 'test street 1',
                1 => 'test street 2',
            ],
            'telephone' => '5123456677'
        ];
        $this->assertResponseFields($customerOrderResponse[0]["billing_address"], $billingAssertionMap);
        $shippingAssertionMap = [
            'firstname' => 'test shipFirst',
            'lastname' => 'test shipLast',
            'city' => 'Montgomery',
            'company' => 'test company',
            'country_code' => 'US',
            'postcode' => '36013',
            'street' => [
                0 => 'test street 1',
                1 => 'test street 2',
            ],
            'region_id' => '1',
            'region' => 'Alabama',
            'telephone' => '3347665522'
        ];
        $this->assertResponseFields($customerOrderResponse[0]["shipping_address"], $shippingAssertionMap);
        $paymentMethodAssertionMap = [
            [
                'name' => 'Check / Money order',
                'type' => 'checkmo',
                'additional_data' => []
            ]
        ];
        $this->assertResponseFields($customerOrderResponse[0]["payment_methods"], $paymentMethodAssertionMap);
        $this->assertEquals(10.75, $customerOrderResponse[0]['items'][0]['product_sale_price']['value']);
        $this->assertEquals(7.5, $customerOrderResponse[0]['total']['taxes'][0]['rate']);
        // Asserting discounts on order item level
        $this->assertEquals(4, $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['value']);
        $this->assertEquals('USD', $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['currency']);
        $this->assertEquals(
            'Discount Label for 10% off',
            $customerOrderResponse[0]['items'][0]['discounts'][0]['label']
        );
        $customerOrderItem = $customerOrderResponse[0];
        $this->assertTotalsWithTaxesAndDiscounts($customerOrderItem['total']);
        $this->deleteOrder();
    }

    /**
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsWithTaxesAndDiscounts(array $customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(4.05, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 58.05, 'currency' =>'USD'],
            'grand_total' => ['value' => 58.05, 'currency' =>'USD'],
            'subtotal' => ['value' => 40, 'currency' =>'USD'],
            'total_tax' => ['value' => 4.05, 'currency' =>'USD'],
            'total_shipping' => ['value' => 20, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 21.5],
                'amount_excluding_tax' => ['value' => 20],
                'total_amount' => ['value' => 20, 'currency' =>'USD'],
                'discounts' => [
                    0 => ['amount'=>['value'=> 2, 'currency' =>'USD']]
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
                0 => ['amount' => [ 'value' => 6, 'currency' =>'USD'],
                    'label' => 'Discount Label for 10% off'
                ]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
    }

    /**
     *  Verify the customer order with tax, discount with shipping tax class set for calculation setting
     *
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_al.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     */
    public function testCustomerOrdersSimpleProductWithTaxesAndDiscountsWithTwoRules()
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
        $this->assertEquals(4, $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['value']);
        $this->assertEquals('USD', $customerOrderResponse[0]['items'][0]['discounts'][0]['amount']['currency']);
        $this->assertEquals(
            'Discount Label for 10% off',
            $customerOrderResponse[0]['items'][0]['discounts'][0]['label']
        );
        $customerOrderItem = $customerOrderResponse[0];
        $this->assertTotalsWithTaxesAndDiscountsWithTwoRules($customerOrderItem['total']);
        $this->deleteOrder();
    }

    /**
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsWithTaxesAndDiscountsWithTwoRules(array $customerOrderItemTotal): void
    {
        $this->assertCount(2, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(4.05, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        $secondTaxData = $customerOrderItemTotal['taxes'][1];
        $this->assertEquals('USD', $secondTaxData['amount']['currency']);
        $this->assertEquals(2.97, $secondTaxData['amount']['value']);
        $this->assertEquals('US-AL-*-Rate-1', $secondTaxData['title']);
        $this->assertEquals(5.5, $secondTaxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 61.02, 'currency' =>'USD'],
            'grand_total' => ['value' => 61.02, 'currency' =>'USD'],
            'subtotal' => ['value' => 40, 'currency' =>'USD'],
            'total_tax' => ['value' => 7.02, 'currency' =>'USD'],
            'total_shipping' => ['value' => 20, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 22.6],
                'amount_excluding_tax' => ['value' => 20],
                'total_amount' => ['value' => 20, 'currency' =>'USD'],
                'discounts' => [
                    0 => ['amount'=>['value'=> 2, 'currency' =>'USD']]
                ],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 1.35],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ],
                    1 => [
                        'amount'=>['value' => 0.99],
                        'title' => 'US-AL-*-Rate-1',
                        'rate' => 5.5
                    ]
                ]
            ],
            'discounts' => [
                0 => ['amount' => [ 'value' => 6, 'currency' =>'USD'],
                    'label' => 'Discount Label for 10% off'
                ]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
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
        $this->assertCount(6, $response['customer']['orders']['items']);
        $customerOrderItems = $response['customer']['orders']['items'];
        $expectedOrderNumbers = ['100000002', '100000004', '100000005','100000006', '100000007', '100000008'];
        $actualOrdersFromResponse = [];
        foreach ($customerOrderItems as $order) {
            array_push($actualOrdersFromResponse, $order['number']);
        }
        $this->assertEquals($expectedOrderNumbers, $actualOrdersFromResponse, 'Order numbers do not match');
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
        $this->assertCount($response['customer']['orders']['total_count'], $response['customer']['orders']['items']);
    }

    /**
     * @return void
     * @throws AuthenticationException
     */
    #[
        DataFixture(Customer::class, ['email' => 'customer@example.com'], 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart2'),
        DataFixture(ProductFixture::class, ['sku' => '100000002', 'price' => 10], 'p2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart2.id$', 'product_id' => '$p2.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart2.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart2.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart2.id$'], 'or2'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart3'),
        DataFixture(ProductFixture::class, ['sku' => '100000003', 'price' => 10], 'p3'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart3.id$', 'product_id' => '$p3.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart3.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart3.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart3.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart3.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart3.id$'], 'or3'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart4'),
        DataFixture(ProductFixture::class, ['sku' => '100000004', 'price' => 10], 'p4'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart4.id$', 'product_id' => '$p4.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart4.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart4.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart4.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart4.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart4.id$'], 'or4'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart5'),
        DataFixture(ProductFixture::class, ['sku' => '100000005', 'price' => 10], 'p5'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart5.id$', 'product_id' => '$p5.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart5.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart5.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart5.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart5.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart5.id$'], 'or5'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart6'),
        DataFixture(ProductFixture::class, ['sku' => '100000006', 'price' => 10], 'p6'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart6.id$', 'product_id' => '$p6.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart6.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart6.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart6.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart6.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart6.id$'], 'or6'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart7'),
        DataFixture(ProductFixture::class, ['sku' => '100000007', 'price' => 10], 'p7'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart7.id$', 'product_id' => '$p7.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart7.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart7.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart7.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart7.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart7.id$'], 'or7'),
    ]

    #[
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], 'cart8'),
        DataFixture(ProductFixture::class, ['sku' => '100000008', 'price' => 10], 'p8'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart8.id$', 'product_id' => '$p8.id$']),
        DataFixture(SetBillingAddress::class, ['cart_id' => '$cart8.id$']),
        DataFixture(SetShippingAddress::class, ['cart_id' => '$cart8.id$']),
        DataFixture(SetDeliveryMethod::class, ['cart_id' => '$cart8.id$']),
        DataFixture(SetPaymentMethod::class, ['cart_id' => '$cart8.id$']),
        DataFixture(PlaceOrder::class, ['cart_id' => '$cart8.id$'], 'or8'),
    ]
    public function testGetCustomerDescendingSortedOrders()
    {
        $query = <<<QUERY
{
  customer {
    orders(
      sort: {
        sort_field: CREATED_AT,
        sort_direction: DESC
      }
    ) {
      items {
        id
        number
         status
         order_date
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

        $order2 = $this->fixtures->get('or2')->getIncrementId();
        $order3 = $this->fixtures->get('or3')->getIncrementId();
        $order4 = $this->fixtures->get('or4')->getIncrementId();
        $order5 = $this->fixtures->get('or5')->getIncrementId();
        $order6 = $this->fixtures->get('or6')->getIncrementId();
        $order7 = $this->fixtures->get('or7')->getIncrementId();
        $order8 = $this->fixtures->get('or8')->getIncrementId();

        $expectedOrderNumbersOptions = [$order8, $order7, $order6, $order5, $order4, $order3, $order2 ];
        $expectedOrderNumbers = $scalarTemp = [];
        $compDate = $prevComKey = '';
        foreach ($expectedOrderNumbersOptions as $comKey => $comData) {
            if ($compDate == $customerOrderItemsInResponse[$comKey]['order_date']) {
                $expectedOrderNumbers[] = $expectedOrderNumbers[$prevComKey];
                $scalarTemp = (array)$comData;
                $expectedOrderNumbers[$prevComKey] = $scalarTemp[0];
            } else {
                $scalarTemp = (array)$comData;
                $expectedOrderNumbers[] = $scalarTemp[0];
            }
            $prevComKey = $comKey;
            $compDate = $customerOrderItemsInResponse[$comKey]['order_date'];
        }

        foreach ($expectedOrderNumbers as $key => $data) {
            $orderItemInResponse = $customerOrderItemsInResponse[$key];
            $this->assertEquals(
                $data,
                $orderItemInResponse['number'],
                "The order number is different than the expected for order - {$data}"
            );
        }
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GraphQl/Sales/_files/orders_with_customer.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetMultipleCustomerOrdersQueryWithDefaultPagination()
    {
        $orderNumbers = ['100000007', '100000008'];
        $query = <<<QUERY
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
      total{
        base_grand_total {value currency}
        grand_total {value currency}
        subtotal {value currency}
        total_shipping{value}
        total_tax{value currency}
        taxes {amount {currency value} title rate}
        total_shipping{value}
        shipping_handling{
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
        $this->assertArrayNotHasKey('errors', $response);
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

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $orderNumbers, 'in')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        $key = 0;
        foreach ($orders as $order) {
            $orderId = base64_encode($order->getEntityId());
            $orderNumber = $order->getIncrementId();
            $orderItemInResponse = $customerOrderItemsInResponse[$key];
            $this->assertNotEmpty($orderItemInResponse['id']);
            $this->assertEquals($orderId, $orderItemInResponse['id']);
            $this->assertEquals($orderNumber, $orderItemInResponse['number']);
            $this->assertEquals('Processing', $orderItemInResponse['status']);
            $this->assertEquals(5, $orderItemInResponse['total']['shipping_handling']['total_amount']['value']);
            $this->assertEquals(5, $orderItemInResponse['total']['total_shipping']['value']);
            $this->assertEquals(5, $orderItemInResponse['total']['total_tax']['value']);
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
        $this->assertEquals(0, $responseWithWrongCustomer['customer']['orders']['total_count']);
        $this->assertEmpty($responseWithWrongCustomer['customer']['orders']['items']);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $responseWithCorrectCustomer = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $this->assertEquals(1, $responseWithCorrectCustomer['customer']['orders']['total_count']);
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
  customer {
    orders(filter: {number: {eq: "{$orderNumber}"}}) {
      items {
        number
        items {
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
          total_shipping {
            value
          }
          shipping_handling {
            amount_including_tax {
              value
            }
            amount_excluding_tax {
              value
            }
            total_amount {
              value
            }
            taxes {
              amount {
                value
              }
              title
              rate
            }
          }
          subtotal {
            value
            currency
          }
          taxes {
            amount {
              value
              currency
            }
            title
            rate
          }
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
        $this->assertArrayNotHasKey('errors', $response);
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
    customer {
           orders(filter:{number:{eq:"{$orderNumber}"}}) {
            page_info {current_page page_size total_pages}
             total_count
             items {
               number
               items{ product_sku }
               total {
                 base_grand_total{value currency}
                 grand_total{value currency}
                 subtotal { value currency }
                 shipping_handling
                 {
                   total_amount{value currency}
                 }
               }
             }
           }
         }
       }
QUERY;

        $headers = array_merge(
            $this->customerAuthenticationHeader->execute('customer@example.com', 'password'),
            ['Store' => $store]
        );
        $response = $this->graphQlQuery($query, [], '', $headers);
        $this->assertArrayHasKey('customer', $response);
        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertCount($expectedCount, $response['customer']['orders']['items']);
        $this->assertArrayHasKey('total_count', $response['customer']['orders']);
        $this->assertEquals($expectedCount, (int)$response['customer']['orders']['total_count']);
        $this->assertTotals($response, $expectedCount);
    }

    /**
     * @param array $response
     * @param int $expectedCount
     */
    private function assertTotals(array $response, int $expectedCount): void
    {
        $assertionMap = [
            'base_grand_total' => ['value' => 100, 'currency' =>'USD'],
            'grand_total' => ['value' => 100, 'currency' =>'USD'],
            'subtotal' => ['value' => 110, 'currency' =>'USD'],
            'shipping_handling' => [
                'total_amount' => ['value' => 10, 'currency' =>'USD']
            ]
        ];
        if ($expectedCount === 0) {
            $this->assertEmpty($response['customer']['orders']['items']);
        } else {
            $customerOrderItemTotal = $response['customer']['orders']['items'][0]['total'];
            $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
        }
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
     * Verify that the customer order has the tax information on shipping and totals
     *
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
        $this->assertTotalsAndShippingWithExcludedTaxSetting($customerOrderItem['total']);
        $this->deleteOrder();
    }

    /**
     * Assert totals and shipping amounts with taxes excluded
     *
     * @param $customerOrderItemTotal
     */
    private function assertTotalsAndShippingWithExcludedTaxSetting($customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(2.25, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 32.25, 'currency' =>'USD'],
            'grand_total' => ['value' => 32.25, 'currency' =>'USD'],
            'total_tax' => ['value' => 2.25, 'currency' =>'USD'],
            'subtotal' => ['value' => 20, 'currency' =>'USD'],
            'discounts' => [],
            'total_shipping' => ['value' => 10, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 10.75],
                'amount_excluding_tax' => ['value' => 10],
                'total_amount' => ['value' => 10, 'currency' =>'USD'],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 0.75],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ]
                ],
                'discounts' =>[]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
    }

    /**
     * Verify that the customer order has the tax information on shipping and totals
     *
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
        $this->assertTotalsAndShippingWithTaxes($customerOrderItem['total']);
        $this->deleteOrder();
    }

    /**
     * Check order totals an shipping amounts with taxes
     *
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsAndShippingWithTaxes(array $customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);

        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(2.25, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        unset($customerOrderItemTotal['shipping_handling']['discounts']);
        $assertionMap = [
            'base_grand_total' => ['value' => 32.25, 'currency' =>'USD'],
            'grand_total' => ['value' => 32.25, 'currency' =>'USD'],
            'total_tax' => ['value' => 2.25, 'currency' =>'USD'],
            'subtotal' => ['value' => 20, 'currency' =>'USD'],
            'total_shipping' => ['value' => 10, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 10.75],
                'amount_excluding_tax' => ['value' => 10],
                'total_amount' => ['value' => 10, 'currency' =>'USD'],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 0.75],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ]
                ]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
    }

    /**
     * Create an empty cart with GraphQl mutation
     *
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
     * Add product to cart with GraphQl query
     *
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

    /**
     * Set billing address on cart with GraphQL mutation
     *
     * @param string $cartId
     * @return void
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
     * Set shipping address on cart with GraphQl query
     *
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
     * Set shipping method on cart with GraphQl mutation
     *
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
     * Set payment method on cart with GrpahQl mutation
     *
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
     * Place order using GraphQl mutation
     *
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
    private function getCustomerOrderQuery($orderNumber): array
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
           payment_methods
           {
             name
             type
             additional_data
             {
              name
              value
              }
           }
           shipping_address {
           ... address
           }
           billing_address {
           ... address
           }
           items{
             product_name
             product_sku
             quantity_ordered
             product_sale_price {value}
             discounts {amount{value currency} label}
           }
           total {
             base_grand_total{value currency}
             grand_total{value currency}
             total_tax{value currency}
             subtotal { value currency }
             taxes {amount{value currency} title rate}
             discounts {amount{value currency} label}
             total_shipping{value currency}
             shipping_handling
             {
               amount_including_tax{value}
               amount_excluding_tax{value}
               total_amount{value currency}
               taxes {amount{value} title rate}
               discounts {amount{value currency}}
             }

           }
         }
       }
     }
   }

   fragment address on OrderAddress {
          firstname
          lastname
          city
          company
          country_code
          fax
          middlename
          postcode
          street
          region
          region_id
          telephone
          vat_id
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
        return $response['customer']['orders']['items'];
    }

    /**
     * Get customer order query
     *
     * @param string $orderNumber
     * @return array
     */
    private function getCustomerOrderQueryOnSimpleProducts($orderNumber): array
    {
        $query =
            <<<QUERY
{
  customer
  {
   orders(filter:{number:{eq:"{$orderNumber}"}}) {
    total_count
    items
    {
      id
      number
      status
      order_date
      payment_methods
      {
        name
        type
        additional_data
        {
         name
         value
         }
      }
      shipping_address {
         ... address
      }
      billing_address {
      ... address
      }
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

fragment address on OrderAddress {
          firstname
          lastname
          city
          company
          country_code
          fax
          middlename
          postcode
          street
          region
          region_id
          telephone
          vat_id
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
        return $response;
    }

    /**
     * Clean up orders
     *
     * @return void
     */
    private function deleteOrder(): void
    {
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(Collection::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
