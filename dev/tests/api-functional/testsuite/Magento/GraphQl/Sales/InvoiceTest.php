<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\GraphQl\GetCustomerAuthenticationHeader;

/**
 * Tests the Invoice query
 */
class InvoiceTest extends GraphQlAbstract
{
    /** @var GetCustomerAuthenticationHeader */
    private $customerAuthenticationHeader;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    protected function setUp(): void
    {
        $this->customerAuthenticationHeader
            = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class);
        $this->orderRepository = Bootstrap::getObjectManager()->get(OrderRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_invoice_with_two_products_and_custom_options.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSingleInvoiceForLoggedInCustomerQuery()
    {
        $response = $this->getCustomerInvoicesBasedOnOrderNumber('100000001');
        $expectedOrdersData = [
            'status' => 'Processing',
            'grand_total' => 100.00
        ];
        $expectedInvoiceData = [
            [
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10,
                            'currency' => 'USD'
                        ],
                        'quantity_invoiced' => 1,
                        'discounts' => []
                    ],
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10,
                            'currency' => 'USD'
                        ],
                        'quantity_invoiced' => 1,
                        'discounts' => []
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 100,
                        'currency' => 'USD'
                    ],
                    'grand_total' => [
                        'value' => 100,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ],
                    'shipping_handling' => [
                        'total_amount' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'amount_including_tax' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'amount_excluding_tax' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'taxes' => [],
                        'discounts' => []
                    ],
                    'taxes' => [],
                    'discounts' => [],
                    'base_grand_total' => [
                        'value' => 100,
                        'currency' => 'EUR'
                    ],
                    'total_tax' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ],
                ]
            ]
        ];
        $this->assertOrdersData($response, $expectedOrdersData);
        $invoices = $response[0]['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoiceData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_multiple_invoices_with_two_products_and_custom_options.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMultipleInvoiceForLoggedInCustomerQuery()
    {
        $response = $this->getCustomerInvoicesBasedOnOrderNumber('100000002');
        $expectedOrdersData = [
            'status' => 'Processing',
            'grand_total' => 60.00
        ];
        $expectedInvoiceData = [
            [
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10,
                            'currency' => 'USD'
                        ],
                        'quantity_invoiced' => 3,
                        'discounts'=> []
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 30,
                        'currency' => 'USD'
                    ],
                    'grand_total' => [
                        'value' => 50,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 20,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 50,
                        'currency' => 'EUR'
                    ],
                    'total_tax' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ],
                    'shipping_handling' => [
                        'total_amount' => [
                            'value' => 20,
                            'currency' => 'USD'
                        ],
                        'amount_including_tax' => [
                            'value' => 25,
                            'currency' => 'USD'
                        ],
                        'amount_excluding_tax' => [
                            'value' => 20,
                            'currency' => 'USD'
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'taxes' => [],
                    'discounts' => [],
                ]
            ],
            [
                'items' => [
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10,
                            'currency' => 'USD'
                        ],
                        'quantity_invoiced' => 1,
                        'discounts' => []
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 10,
                        'currency' => 'USD'
                    ],
                    'grand_total' => [
                        'value' => 10,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 0,
                        'currency' => 'EUR'
                    ],
                    'total_tax' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ],
                    'shipping_handling' => [
                        'total_amount' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'amount_including_tax' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'amount_excluding_tax' => [
                            'value' => 0,
                            'currency' => 'USD'
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'taxes' => [],
                    'discounts' => [],
                ]
            ]
        ];
        $this->assertOrdersData($response, $expectedOrdersData);
        $invoices = $response[0]['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoiceData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customers_with_invoices.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMultipleCustomersWithInvoicesQuery()
    {
        $query =
            <<<QUERY
{
  customer
  {
  orders {
    items {
      status
      total {
        grand_total {
          value
          currency
        }
      }
      invoices {
          items{
            product_name
            product_sku
            product_sale_price {
              value
              currency
            }
            quantity_invoiced
      }
      total {
        subtotal {
          value
          currency
        }
        grand_total {
          value
          currency
        }
        total_shipping {
          value
          currency
        }
      }
    }
}
}
}
}
QUERY;

        $currentEmail = 'customer@search.example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->customerAuthenticationHeader->execute($currentEmail, $currentPassword)
        );
        $expectedOrdersData = [
            'status' => 'Processing',
            'grand_total' => 100.00
        ];

        $expectedInvoiceData = [
            [
                'items' => [
                    [
                        'product_name' => 'Simple Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10,
                            'currency' => 'USD'
                        ],
                        'quantity_invoiced' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 100,
                        'currency' => 'USD'
                    ],
                    'grand_total' => [
                        'value' => 100,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 0,
                        'currency' => 'USD'
                    ]
                ]
            ]
        ];
        $this->assertOrdersData($response['customer']['orders']['items'], $expectedOrdersData);
        $invoices = $response['customer']['orders']['items'][0]['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoiceData);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_and_order_display_settings.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     */
    public function testInvoiceForCustomerWithTaxesAndDiscounts()
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
        $this->prepareInvoice($orderNumber, 2);
        $customerOrderResponse = $this->getCustomerInvoicesBasedOnOrderNumber($orderNumber);
        $customerOrderItem = $customerOrderResponse[0];
        $invoice = $customerOrderItem['invoices'][0];
        $this->assertEquals(3, $invoice['total']['discounts'][0]['amount']['value']);
        $this->assertEquals('USD', $invoice['total']['discounts'][0]['amount']['currency']);
        $this->assertEquals(
            'Discount Label for 10% off',
            $invoice['total']['discounts'][0]['label']
        );
        $this->assertTotalsAndShippingWithTaxesAndDiscounts($customerOrderItem['invoices'][0]['total']);
        $this->deleteOrder();
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_and_order_display_settings.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     */
    public function testPartialInvoiceForCustomerWithTaxesAndDiscounts()
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
        $this->prepareInvoice($orderNumber, 1);
        $customerOrderResponse = $this->getCustomerInvoicesBasedOnOrderNumber($orderNumber);
        $customerOrderItem = $customerOrderResponse[0];
        $invoice = $customerOrderItem['invoices'][0];
        $invoiceItem = $invoice['items'][0];
        $this->assertEquals(1, $invoiceItem['discounts'][0]['amount']['value']);
        $this->assertEquals('USD', $invoiceItem['discounts'][0]['amount']['currency']);
        $this->assertEquals('Discount Label for 10% off', $invoiceItem['discounts'][0]['label']);
        $this->assertEquals(2, $invoice['total']['discounts'][0]['amount']['value']);
        $this->assertEquals('USD', $invoice['total']['discounts'][0]['amount']['currency']);
        $this->assertEquals(
            'Discount Label for 10% off',
            $invoice['total']['discounts'][0]['label']
        );
        $this->assertTotalsAndShippingWithTaxesAndDiscountsForOneQty($customerOrderItem['invoices'][0]['total']);
        $this->deleteOrder();
    }

    /**
     * Prepare invoice for the order
     *
     * @param string $orderNumber
     * @param int|null $qty
     */
    private function prepareInvoice(string $orderNumber, int $qty = null)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = Bootstrap::getObjectManager()
            ->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($orderNumber);
        $orderItem = current($order->getItems());
        $orderService = Bootstrap::getObjectManager()->create(
            \Magento\Sales\Api\InvoiceManagementInterface::class
        );
        $invoice = $orderService->prepareInvoice($order, [$orderItem->getId() => $qty]);
        $invoice->register();
        $order = $invoice->getOrder();
        $order->setIsInProcess(true);
        $transactionSave = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\DB\Transaction::class);
        $transactionSave->addObject($invoice)->addObject($order)->save();
    }

    /**
     * Check order totals an shipping amounts with taxes
     *
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsAndShippingWithTaxesAndDiscounts(array $customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(2.03, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 29.1, 'currency' =>'USD'],
            'grand_total' => ['value' => 29.1, 'currency' =>'USD'],
            'total_tax' => ['value' => 2.03, 'currency' =>'USD'],
            'subtotal' => ['value' => 20, 'currency' =>'USD'],
            'total_shipping' => ['value' => 10, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 10.75, 'currency' =>'USD'],
                'amount_excluding_tax' => ['value' => 10, 'currency' =>'USD'],
                'total_amount' => ['value' => 10, 'currency' =>'USD'],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 0.68],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ]
                ],
                 'discounts'=> [
                     0 => ['amount'=>['value' => 1, 'currency'=> 'USD']]
                 ],
            ]
        ];
        $this->assertResponseFields($customerOrderItemTotal, $assertionMap);
    }

    /**
     * Check order totals an shipping amounts with taxes
     *
     * @param array $customerOrderItemTotal
     */
    private function assertTotalsAndShippingWithTaxesAndDiscountsForOneQty(array $customerOrderItemTotal): void
    {
        $this->assertCount(1, $customerOrderItemTotal['taxes']);
        $taxData = $customerOrderItemTotal['taxes'][0];
        $this->assertEquals('USD', $taxData['amount']['currency']);
        $this->assertEquals(1.36, $taxData['amount']['value']);
        $this->assertEquals('US-TEST-*-Rate-1', $taxData['title']);
        $this->assertEquals(7.5, $taxData['rate']);

        unset($customerOrderItemTotal['taxes']);
        $assertionMap = [
            'base_grand_total' => ['value' => 19.43, 'currency' =>'USD'],
            'grand_total' => ['value' => 19.43, 'currency' =>'USD'],
            'total_tax' => ['value' => 1.36, 'currency' =>'USD'],
            'subtotal' => ['value' => 10, 'currency' =>'USD'],
            'total_shipping' => ['value' => 10, 'currency' =>'USD'],
            'shipping_handling' => [
                'amount_including_tax' => ['value' => 10.75, 'currency' =>'USD'],
                'amount_excluding_tax' => ['value' => 10, 'currency' =>'USD'],
                'total_amount' => ['value' => 10, 'currency' =>'USD'],
                'taxes'=> [
                    0 => [
                        'amount'=>['value' => 0.68],
                        'title' => 'US-TEST-*-Rate-1',
                        'rate' => 7.5
                    ]
                ],
                 'discounts'=> [['amount'=>['value' => 1, 'currency'=> 'USD']]
                 ],
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
    private function getCustomerInvoicesBasedOnOrderNumber($orderNumber): array
    {
        $query =
            <<<QUERY
{
     customer {
       email
       orders(filter:{number:{eq:"{$orderNumber}"}}) {
         total_count
         items {
           status
           total {
           grand_total{value currency}
           }
           invoices {
              items{
              product_name product_sku product_sale_price{value currency}quantity_invoiced
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
               amount_including_tax{value currency}
               amount_excluding_tax{value currency}
               total_amount{value currency}
               taxes {amount{value} title rate}
               discounts {amount{value currency}}
             }
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
        return $response['customer']['orders']['items'];
    }

    private function assertOrdersData($response, $expectedOrdersData): void
    {
        $actualData = $response[0];
        $this->assertEquals(
            $expectedOrdersData['grand_total'],
            $actualData['total']['grand_total']['value'],
            "grand_total is different than the expected for order"
        );
        $this->assertEquals(
            $expectedOrdersData['status'],
            $actualData['status'],
            "status is different than the expected for order"
        );
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
