<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for credit memo functionality
 */
class CreditmemoTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAuthenticationHeader = Bootstrap::getObjectManager()->get(
            GetCustomerAuthenticationHeader::class
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_creditmemo_with_two_items.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreditMemoForLoggedInCustomerQuery(): void
    {
        $query =
            <<<QUERY
query {
  customer {
    orders {
        items {
            credit_memos {
                comments {
                    message
                }
                items {
                    product_name
                    product_sku
                    product_sale_price {
                        value
                    }
                    quantity_refunded
                }
                total {
                    subtotal {
                        value
                    }
                    base_grand_total  {
                        value
                    }
                    grand_total {
                        value
                    }
                    total_shipping {
                        value
                    }
                    shipping_handling {
                         amount_including_tax{value}
                         amount_excluding_tax{value}
                         total_amount{value}
                         taxes {amount{value} title rate}
                         discounts {amount{value} label}
                    }
                    adjustment {
                        value
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

        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'some_comment'],
                    ['message' => 'some_other_comment']
                ],
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_refunded' => 1
                    ],
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'quantity_refunded' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 20
                    ],
                    'grand_total' => [
                        'value' => 20
                    ],
                    'base_grand_total' => [
                        'value' => 10
                    ],
                    'total_shipping' => [
                        'value' => 0
                    ],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 0
                        ],
                        'amount_excluding_tax' => [
                            'value' => 0
                        ],
                        'total_amount' => [
                            'value' => 0
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'adjustment' => [
                        'value' => 1.23
                    ]
                ]
            ]
        ];

        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);

        $creditMemos = $firstOrderItem['credit_memos'] ?? [];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
    }
}
