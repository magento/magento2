<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\GraphQl\GetCustomerAuthenticationHeader;

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

    /** @var GetCustomerAuthenticationHeader */
    private $customerAuthenticationHeader;

    protected function setUp():void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerTokenService = $objectManager->get(CustomerTokenServiceInterface::class);
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
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
        $response = $this->graphQlQuery($query, [], '', $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword));

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        $this->assertNotEmpty($response['customer']['orders']['items']);
        $customerOrderItemsInResponse = $response['customer']['orders']['items'][0];
        $expectedCount = count($response['customer']['orders']['items']);
        $this->assertArrayHasKey('items', $customerOrderItemsInResponse);
        $this->assertNotEmpty($customerOrderItemsInResponse['items']);

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
        $actualOrderItemsFromResponse = $customerOrderItemsInResponse['items'][0];
        $this->assertEquals($expectedOrderItems, $actualOrderItemsFromResponse);
        $actualOrderTotalFromResponse = $response['customer']['orders']['items'][0]['total'];
        $expectedOrderTotal =
            [
                'base_grand_total' => ['value'=> 120,'currency' =>'USD'],
                'grand_total' => ['value'=> 120,'currency' =>'USD'],
                'subtotal' => ['value'=> 120,'currency' =>'USD']
            ];
        $this->assertEquals($expectedOrderTotal, $actualOrderTotalFromResponse,'Totals do not match');
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
        $response = $this->graphQlQuery($query, [], '', $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword));
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
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid match filter. Minimum length is 3.');
        $this->graphQlQuery($query, [], '', $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword));
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
      items{
        quantity_ordered
        product_sku
        product_name
        product_type
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
        $response = $this->graphQlQuery($query, [], '', $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword));

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
        $response = $this->graphQlQuery($query, [], '', $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword));
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
            array_merge($this->customerAuthenticationHeader->execute($currentEmail, $currentPassword), ['Store' => $store])
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
                5,
                $response['customer']['orders']['items'][0]['total']['taxes'][0]['amount']['value']
            );
            $this->assertEquals(
                'USD',
                $response['customer']['orders']['items'][0]['total']['taxes'][0]['amount']['currency']
            );
        }

    }
}
