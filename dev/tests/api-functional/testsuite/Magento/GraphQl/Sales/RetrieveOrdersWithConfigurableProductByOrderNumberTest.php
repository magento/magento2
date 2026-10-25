<?php
/**
 * Copyright 2025 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\Quote\Test\Fixture\QuoteIdMask;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Exception\AuthenticationException;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
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

    #[
        DataFixture(Customer::class, ['email' => 'customer@example.com'], as: 'customer'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(AttributeFixture::class, as: 'attribute'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attribute$'], '_links' => ['$product$']],
            'configurable_product'
        ),
        DataFixture(
            CustomerCart::class,
            [
                'customer_id' => '$customer.id$'
            ],
            'quote'
        ),
        DataFixture(QuoteIdMask::class, ['cart_id' => '$quote.id$'], 'quoteIdMask'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            [
                'cart_id' => '$quote.id$',
                'product_id' => '$configurable_product.id$',
                'child_product_id' => '$product.id$',
                'qty' => 1
            ],
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$quote.id$']),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$quote.id$'], 'order'),
    ]
    public function testGetCustomerOrderConfigurableProduct(): void
    {
        $order = DataFixtureStorageManager::getStorage()->get('order');
        $orderNumber = $order->getIncrementId();
        $product = DataFixtureStorageManager::getStorage()->get('product');
        $configurableProduct = DataFixtureStorageManager::getStorage()->get('configurable_product');
        $customerOrderResponse = $this->getCustomerOrderQueryConfigurableProduct($orderNumber);
        $customerOrderItems = $customerOrderResponse[0];
        $configurableItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            $product->getSku(),
            $configurableItemInTheOrder['product_sku']
        );

        $expectedConfigurableOptions = [
            '__typename' => 'ConfigurableOrderItem',
            'product_sku' => $product->getSku(),
            'product_name' => $configurableProduct->getName(),
            'parent_sku' => $configurableProduct->getSku(),
            'product_url_key' => $configurableProduct->getUrlKey(),
            'quantity_ordered' => 1
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
