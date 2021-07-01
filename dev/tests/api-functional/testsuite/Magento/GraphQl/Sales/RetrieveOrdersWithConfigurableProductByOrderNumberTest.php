<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for orders with configurable product
 */
class RetrieveOrdersWithConfigurableProductByOrderNumberTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * Test customer order details with configurable product with child items
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_with_configurable_product.php
     */
    public function testGetCustomerOrderConfigurableProduct(): void
    {
        $orderNumber = '100000001';
        $customerOrderResponse = $this->getCustomerOrderQueryConfigurableProduct($orderNumber);
        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals('Pending Payment', $customerOrderItems['status']);
        $configurableItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            'simple_10',
            $configurableItemInTheOrder['product_sku']
        );

        $expectedConfigurableOptions = [
            '__typename' => 'ConfigurableOrderItem',
            'product_sku' => 'simple_10',
            'product_name' => 'Configurable Product',
            'parent_sku' => 'configurable',
            'product_url_key' => 'configurable-product',
            'quantity_ordered' => 2
        ];
        $this->assertEquals($expectedConfigurableOptions, $configurableItemInTheOrder);
    }

    /**
     * Get customer order query for configurable order items
     *
     * @param $orderNumber
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerOrderQueryConfigurableProduct($orderNumber): array
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
           items {
             __typename
             product_sku
             product_name
             product_url_key
             quantity_ordered
             ... on ConfigurableOrderItem {
               parent_sku
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
               discounts{amount{value}}
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
}
