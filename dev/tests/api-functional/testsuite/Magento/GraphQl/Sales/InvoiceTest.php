<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Class Invoice Test
 */
class InvoiceTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_invoice_with_two_products_and_custom_options.php
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
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $expectedData = [
            [
                'order_number' => '100000001',
                'status' => 'Processing',
                'grand_total' => 100.00
            ]
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

        $actualData = $response['customer']['orders']['items'];

        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['order_number'],
                $actualData[$key]['order_number'],
                "order_number is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['grand_total'],
                $actualData[$key]['grand_total'],
                "grand_total is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['status'],
                $actualData[$key]['status'],
                "status is different than the expected for order - " . $data['order_number']
            );
            $invoices = $actualData[$key]['invoices'];
            $this->assertResponseFields($invoices, $expectedInvoiceData);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_multiple_invoices_with_two_products_and_custom_options.php
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
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $expectedData = [
            [
                'order_number' => '100000002',
                'status' => 'Processing',
                'grand_total' => 50.00
            ]
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
                        'value' => 30
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

        $actualData = $response['customer']['orders']['items'];

        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['order_number'],
                $actualData[$key]['order_number'],
                "order_number is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['grand_total'],
                $actualData[$key]['grand_total'],
                "grand_total is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['status'],
                $actualData[$key]['status'],
                "status is different than the expected for order - " . $data['order_number']
            );
            $invoices = $actualData[$key]['invoices'];
            $this->assertResponseFields($invoices, $expectedInvoiceData);
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customers_with_invoices.php
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

        $currentEmail = 'customer@search.example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $expectedData = [
            [
                'order_number' => '100000001',
                'status' => 'Processing',
                'grand_total' => 100.00
            ]
        ];

        $expectedInvoiceData = [
            [
                'items' => [
                    [
                        'product_name' => 'Simple Product',
                        'product_sku' => 'simple',
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

        $actualData = $response['customer']['orders']['items'];

        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['order_number'],
                $actualData[$key]['order_number'],
                "order_number is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['grand_total'],
                $actualData[$key]['grand_total'],
                "grand_total is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['status'],
                $actualData[$key]['status'],
                "status is different than the expected for order - " . $data['order_number']
            );
            $invoices = $actualData[$key]['invoices'];
            $this->assertResponseFields($invoices, $expectedInvoiceData);
        }
    }

    /**
     */
    public function testOrdersQueryNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
{
  customer {
      orders {
        items {
          increment_id
          grand_total
        }
      }
  }
}
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}
