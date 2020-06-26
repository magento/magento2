<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

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

    protected function setUp(): void
    {
        $this->customerAuthenticationHeader
            = Bootstrap::getObjectManager()->get(GetCustomerAuthenticationHeader::class);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_invoice_with_two_products_and_custom_options.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSingleInvoiceForLoggedInCustomerQuery()
    {
        $query =
            <<<QUERY
query {
  customer
  {
  orders {
    items {
      order_number
      grand_total
      status
      invoices {
          items{
            product_name
            product_sku
            product_sale_price {
              value
            }
            quantity_invoiced
          }
          total {
            subtotal {
              value
            }
            grand_total {
              value
            }
            total_shipping {
              value
            }
      			shipping_handling {
              total_amount {
                value
              }
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

        $expectedOrdersData = [
            'order_number' => '100000001',
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
                            'value' => 10
                        ],
                        'quantity_invoiced' => 1
                    ],
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_invoiced' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 100
                    ],
                    'grand_total' => [
                        'value' => 100
                    ],
                    'total_shipping' => [
                        'value' => 0
                    ],
                    'shipping_handling' => [
                        'total_amount' => [
                            'value' => null
                        ]
                    ]
                ]
            ]
        ];

        $actualData = $response['customer']['orders']['items'][0];

        $this->assertEquals(
            $expectedOrdersData['order_number'],
            $actualData['order_number'],
            "order_number is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['grand_total'],
            $actualData['grand_total'],
            "grand_total is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['status'],
            $actualData['status'],
            "status is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $invoices = $actualData['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoiceData);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_multiple_invoices_with_two_products_and_custom_options.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testMultipleInvoiceForLoggedInCustomerQuery()
    {
        $query =
            <<<QUERY
query {
  customer
  {
  orders {
    items {
      order_number
      grand_total
      status
      invoices {
          items{
            product_name
            product_sku
            product_sale_price {
              value
            }
            quantity_invoiced
      }
      total {
        subtotal {
          value
        }
        grand_total {
          value
        }
        total_shipping {
          value
          currency
        }
        shipping_handling {
          total_amount {
            value
            currency
          }
          amount_including_tax {
            value
            currency
          }
          amount_excluding_tax {
            value
            currency
          }
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

        $expectedOrdersData = [
            'order_number' => '100000002',
            'status' => 'Processing',
            'grand_total' => 50.00
        ];

        $expectedInvoiceData = [
            [
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_invoiced' => 3
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 30
                    ],
                    'grand_total' => [
                        'value' => 50
                    ],
                    'total_shipping' => [
                        'value' => 20,
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
                        ]
                    ]
                ]
            ],
            [
                'items' => [
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_invoiced' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 10
                    ],
                    'grand_total' => [
                        'value' => 10
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
                        ]
                    ]
                ]
            ]
        ];

        $actualData = $response['customer']['orders']['items'][0];
        $this->assertEquals(
            $expectedOrdersData['order_number'],
            $actualData['order_number'],
            "order_number is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['grand_total'],
            $actualData['grand_total'],
            "grand_total is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['status'],
            $actualData['status'],
            "status is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $invoices = $actualData['invoices'];
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
query {
  customer
  {
  orders {
    items {
      order_number
      grand_total
      status
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
            'order_number' => '100000001',
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

        $actualData = $response['customer']['orders']['items'][0];
        $this->assertEquals(
            $expectedOrdersData['order_number'],
            $actualData['order_number'],
            "order_number is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['grand_total'],
            $actualData['grand_total'],
            "grand_total is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $this->assertEquals(
            $expectedOrdersData['status'],
            $actualData['status'],
            "status is different than the expected for order - " . $expectedOrdersData['order_number']
        );
        $invoices = $actualData['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoiceData);
    }
}
