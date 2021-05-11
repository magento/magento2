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
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for orders with configurable product
 */
class RetrieveOrdersWithConfigurableProductByOrderNumberTest extends GraphQlAbstract
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

    protected function tearDown(): void
    {
        $this->deleteOrder();
    }

    /**
     * Test customer order details with configurable product with child items
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_with_configurable_product.php
     */
    public function testGetCustomerOrderConfigurableProduct()
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
            '__typename' => 'OrderItem',
            'product_sku' => 'simple_10',
            'product_name' => 'Configurable Product',
            'parent_sku' => 'configurable',
            'product_url_key' => 'configurable-product',
            'quantity_ordered'=> 2
        ];
        $this->assertEquals($expectedConfigurableOptions, $configurableItemInTheOrder);
    }

    /**
     * Get customer order query for configurable order items
     *
     * @param $orderNumber
     * @return mixed
     * @throws AuthenticationException
     */
    private function getCustomerOrderQueryConfigurableProduct($orderNumber)
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
             parent_sku
             product_url_key
             quantity_ordered
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
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
