<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Registry;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\GraphQl\Sales\Fixtures\CustomerPlaceOrder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for orders with bundle product.
 */
class RetrieveOrdersWithBundleProductOptionsTest extends GraphQlAbstract
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var GetCustomerAuthenticationHeader */
    private $customerAuthenticationHeader;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        $this->deleteOrder();
    }

    /**
     * Test customer order details for bundle product single child item
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_single_dropdown_option.php
     */
    public function testGetCustomerOrderBundleProduct(): void
    {
        $qty = 1;
        $bundleSku = 'bundle-product-single-dropdown-option';
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => $bundleSku, 'quantity' => $qty]
        );
        $customerOrderResponse = $this->getCustomerOrderQueryBundleProduct();
        $customerOrderItems = $customerOrderResponse[0];
        $this->assertEquals("Pending", $customerOrderItems['status']);
        $bundledItemInTheOrder = $customerOrderItems['items'][0];
        $this->assertEquals(
            'bundle-product-single-dropdown-option-simple1',
            $bundledItemInTheOrder['product_sku']
        );
        $this->assertArrayHasKey('bundle_options', $bundledItemInTheOrder);
        $bundleOptionsFromResponse = $bundledItemInTheOrder['bundle_options'];
        $this->assertNotEmpty($bundleOptionsFromResponse);
        $this->assertEquals(1, count($bundleOptionsFromResponse));
        $expectedBundleOptions =
            [
                ['__typename' => 'ItemSelectedBundleOption',
                    'label' => 'Drop Down Option 1',
                    'values' => [
                        [
                            'product_name' => 'Simple Product1',
                            'product_sku' => 'simple1',
                            'price' => [
                                'value' => 1,
                                'currency' => 'USD'
                            ]
                        ]
                    ]
                ],
            ];
        $this->assertEquals($expectedBundleOptions, $bundleOptionsFromResponse);
    }

    /**
     * Get customer order query for bundle order items
     *
     * @return mixed
     * @throws AuthenticationException
     */
    private function getCustomerOrderQueryBundleProduct()
    {
        $query =
            <<<QUERY
            {
             customer {
                orders(pageSize: 20) {
                  items {
                    id
                    order_date
                    items {
                      product_name
                      product_sku
                      ... on BundleOrderItem {
                        bundle_options {
                          __typename
                          label
                          values {
                            product_name
                            product_sku
                            price {
                              value
                              currency
                            }
                          }
                        }
                      }
                    }
                    total {
                      grand_total {
                        value
                        currency
                      }
                    }
                    status
                  }
                }
              }
            }
            QUERY;
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute('customer@example.com', 'password')
        );

        $this->assertArrayHasKey('orders', $response['customer']);
        $this->assertArrayHasKey('items', $response['customer']['orders']);
        return $response['customer']['orders']['items'];
    }

    /**
     * @return void
     */
    private function deleteOrder(): void
    {
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $order Order */
        $orderCollection = Bootstrap::getObjectManager()->create(Collection::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
