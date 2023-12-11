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
use Magento\GraphQl\Sales\Fixtures\CustomerPlaceOrder;
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

    protected function tearDown(): void
    {
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
        //Place order with bundled product
        $qty = 1;
        $bundleSku = 'bundle-product-two-dropdown-options';
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $orderResponse = $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $bundleSku, 'quantity' => $qty]
        );
        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        //End place order with bundled product

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
    }

    /**
     * Test customer order with bundle product and no telephone in address
     *
     * @magentoApiDataFixture Magento/Customer/_files/attribute_telephone_not_required_address.php
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     */
    public function testOrderBundleProductWithNoTelephoneInAddress()
    {
        //Place order with bundled product
        $qty = 1;
        $bundleSku = 'bundle-product-two-dropdown-options';
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $orderResponse = $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $bundleSku, 'quantity' => $qty],
            ['telephone' => '']
        );
        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        $customerOrderResponse = $this->getCustomerOrderQueryBundleProduct($orderNumber);
        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals("Pending", $customerOrderItems['status']);
        $billingAddress = $customerOrderItems['billing_address'];
        $shippingAddress = $customerOrderItems['shipping_address'];
        $this->assertNull($billingAddress['telephone']);
        $this->assertNull($shippingAddress['telephone']);
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
        //Place order with bundled product
        $qty = 4;
        $bundleSku = 'bundle-product-two-dropdown-options';
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $orderResponse = $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $bundleSku, 'quantity' => $qty]
        );
        $orderNumber = $orderResponse['placeOrder']['order']['order_number'];
        //End place order with bundled product

        $customerOrderResponse = $this->getCustomerOrderQueryBundleProduct($orderNumber);
        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals("Pending", $customerOrderItems['status']);

        $bundledItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            'bundle-product-two-dropdown-options-simple1-simple2',
            $bundledItemInTheOrder['product_sku']
        );
        $this->assertEquals(6, $bundledItemInTheOrder['discounts'][0]['amount']['value']);
        $this->assertEquals(
            'Discount Label for 10% off',
            $bundledItemInTheOrder["discounts"][0]['label']
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
                    0 => ['amount'=>['value'=> 2]]
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
                0 => ['amount' => [ 'value' => 8, 'currency' =>'USD'],
                    'label' => 'Discount Label for 10% off'
                ]
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
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
                        discounts{amount{value}}
                        taxes {amount{value} title rate}
                    }
                    discounts {amount{value currency} label}
                }
                billing_address {
                    firstname
                    lastname
                    street
                    city
                    region
                    region_id
                    postcode
                    telephone
                    country_code
                }
                shipping_address {
                    firstname
                    lastname
                    street
                    city
                    region
                    region_id
                    postcode
                    telephone
                    country_code
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
