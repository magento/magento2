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
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for credit memo functionality
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends GraphQlAbstract
{
    /**
     * @var GetCustomerAuthenticationHeader
     */
    private $customerAuthenticationHeader;

    /** @var CreditmemoFactory */
    private $creditMemoFactory;

    /** @var Order */
    private $order;

    /** @var OrderCollection */
    private $orderCollection;

    /** @var CreditmemoService */
    private $creditMemoService;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->customerAuthenticationHeader = $objectManager->get(
            GetCustomerAuthenticationHeader::class
        );
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $this->creditMemoFactory = $objectManager->get(CreditmemoFactory::class);
        $this->order = $objectManager->create(Order::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->orderCollection = $objectManager->get(OrderCollection::class);
        $this->orderRepository = $objectManager->get(OrderRepositoryInterface::class);
        $this->creditMemoService = $objectManager->get(CreditmemoService::class);
    }

    protected function tearDown(): void
    {
        $this->cleanUpCreditMemos();
        $this->deleteOrder();
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_creditmemo_with_two_items.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreditMemoForLoggedInCustomerQuery(): void
    {
        $response = $this->getCustomerOrderWithCreditMemoQuery();

        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'some_comment']
                ],
                'items' => [
                    [
                        'product_name' => 'Simple Related Product',
                        'product_sku' => 'simple',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'discounts' => [],
                        'quantity_refunded' => 1
                    ],
                    [
                        'product_name' => 'Simple Product With Related Product',
                        'product_sku' => 'simple_with_cross',
                        'product_sale_price' => [
                            'value' => 10
                        ],
                        'discounts' => [],
                        'quantity_refunded' => 1
                    ]
                ],
                'total' => [
                    'subtotal' => [
                        'value' => 20
                    ],
                    'grand_total' => [
                        'value' => 20,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 10,
                        'currency' => 'EUR'
                    ],
                    'total_shipping' => [
                        'value' => 0
                    ],
                    'total_tax' => [
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
        $this->assertArrayHasKey('credit_memos', $firstOrderItem);
        $creditMemos = $firstOrderItem['credit_memos'];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
    }

    /**
     * Test customer refund details from order for bundle product with a partial refund
     *
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreditMemoForBundledProductsWithPartialRefund()
    {
        //Place order with bundled product
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $placeOrderResponse = $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => 'bundle-product-two-dropdown-options', 'quantity' => 2]
        );
        $orderNumber = $placeOrderResponse['placeOrder']['order']['order_number'];
        $this->prepareInvoice($orderNumber, 2);

        $order = $this->order->loadByIncrementId($orderNumber);
        /** @var Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $orderItem->setQtyRefunded(1);
        $order->addItem($orderItem);
        $order->save();
        // Create a credit memo
        $creditMemo = $this->creditMemoFactory->createByOrder($order, $order->getData());
        $creditMemo->setOrder($order);
        $creditMemo->setState(1);
        $creditMemo->setSubtotal(15);
        $creditMemo->setBaseSubTotal(15);
        $creditMemo->setShippingAmount(10);
        $creditMemo->setBaseGrandTotal(23);
        $creditMemo->setGrandTotal(23);
        $creditMemo->setAdjustment(-2.00);
        $creditMemo->addComment("Test comment for partial refund", false, true);
        $creditMemo->save();

        $this->creditMemoService->refund($creditMemo, true);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $expectedInvoicesData = [
            [
                'items' => [
                    [
                        'product_name' => 'Bundle Product With Two dropdown options',
                        'product_sku' => 'bundle-product-two-dropdown-options-simple1-simple2',
                        'product_sale_price' => [
                            'value' => 15
                        ],
                        'discounts' => [],
                        'bundle_options' => [
                            [
                                'label' => 'Drop Down Option 1',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product1',
                                        'product_sku' => 'simple1',
                                        'quantity' => 1,
                                        'price' => ['value' => 1, 'currency' => 'USD']
                                    ]
                                ]
                            ],
                            [
                                'label' => 'Drop Down Option 2',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product2',
                                        'product_sku' => 'simple2',
                                        'quantity' => 2,
                                        'price' => ['value' => 2, 'currency' => 'USD']
                                    ]
                                ]
                            ]
                        ],
                        'quantity_invoiced' => 2
                    ],

                ]
            ]
        ];
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for partial refund']
                ],
                'items' => [
                    [
                        'product_name' => 'Bundle Product With Two dropdown options',
                        'product_sku' => 'bundle-product-two-dropdown-options-simple1-simple2',
                        'product_sale_price' => [
                            'value' => 15
                        ],
                        'discounts' => [],
                        'bundle_options' => [
                            [
                                'label' => 'Drop Down Option 1',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product1',
                                        'product_sku' => 'simple1',
                                        'quantity' => 1,
                                        'price' => ['value' => 1, 'currency' => 'USD']
                                    ]
                                ]
                            ],
                            [
                                'label' => 'Drop Down Option 2',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product2',
                                        'product_sku' => 'simple2',
                                        'quantity' => 2,
                                        'price' => ['value' => 2, 'currency' => 'USD']
                                    ]
                                ]
                            ]
                        ],
                        'quantity_refunded' => 1
                    ],

                ],
                'total' => [
                    'subtotal' => [
                        'value' => 15
                    ],
                    'grand_total' => [
                        'value' => 23,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 23,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 10
                    ],
                    'total_tax' => [
                        'value' => 0
                    ],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 10
                        ],
                        'amount_excluding_tax' => [
                            'value' => 10
                        ],
                        'total_amount' => [
                            'value' => 10
                        ],
                        'taxes' => [],
                        'discounts' => [],
                    ],
                    'adjustment' => [
                        'value' => 2
                    ]
                ]
            ]
        ];
        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);

        $this->assertArrayHasKey('invoices', $firstOrderItem);
        $invoices = $firstOrderItem['invoices'];
        $this->assertResponseFields($invoices, $expectedInvoicesData);

        $this->assertArrayHasKey('credit_memos', $firstOrderItem);
        $creditMemos = $firstOrderItem['credit_memos'];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
    }

    /**
     * Test customer order with credit memo details for bundle products with taxes and discounts
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Bundle/_files/bundle_product_two_dropdown_options.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_rule_for_region_1.php
     * @magentoApiDataFixture Magento/SalesRule/_files/cart_rule_10_percent_off_with_discount_on_shipping.php
     * @magentoApiDataFixture Magento/GraphQl/Tax/_files/tax_calculation_shipping_excludeTax_order_display_settings.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testCreditMemoForBundleProductWithTaxesAndDiscounts()
    {
        //Place order with bundled product
        /** @var CustomerPlaceOrder $bundleProductOrderFixture */
        $bundleProductOrderFixture = Bootstrap::getObjectManager()->create(CustomerPlaceOrder::class);
        $placeOrderResponse = $bundleProductOrderFixture->placeOrderWithBundleProduct(
            ['email' => 'customer@example.com', 'password' => 'password'],
            ['sku' => 'bundle-product-two-dropdown-options', 'quantity' => 2]
        );
        $orderNumber = $placeOrderResponse['placeOrder']['order']['order_number'];
        $this->prepareInvoice($orderNumber, 2);
        $order = $this->order->loadByIncrementId($orderNumber);
        /** @var Order\Item $orderItem */
        $orderItem = current($order->getAllItems());
        $orderItem->setQtyRefunded(1);
        $order->addItem($orderItem);
        $order->save();

        $creditMemo = $this->creditMemoFactory->createByOrder($order, $order->getData());
        $creditMemo->setOrder($order);
        $creditMemo->setState(1);
        $creditMemo->setSubtotal(15);
        $creditMemo->setBaseSubTotal(15);
        $creditMemo->setShippingAmount(10);
        $creditMemo->setTaxAmount(1.69);
        $creditMemo->setBaseGrandTotal(24.19);
        $creditMemo->setGrandTotal(24.19);
        $creditMemo->setAdjustment(0.00);
        $creditMemo->setDiscountAmount(-2.5);
        $creditMemo->setDiscountDescription('Discount Label for 10% off');
        $creditMemo->addComment("Test comment for refund with taxes and discount", false, true);
        $creditMemo->save();

        $this->creditMemoService->refund($creditMemo, true);
        $response = $this->getCustomerOrderWithCreditMemoQuery();
        $expectedCreditMemoData = [
            [
                'comments' => [
                    ['message' => 'Test comment for refund with taxes and discount']
                ],
                'items' => [
                    [
                        'product_name' => 'Bundle Product With Two dropdown options',
                        'product_sku' => 'bundle-product-two-dropdown-options-simple1-simple2',
                        'product_sale_price' => [
                            'value' => 15
                        ],
                        'discounts' => [
                            [
                                'amount' => [
                                    'value' => 3,
                                    'currency' => "USD"
                                ],
                                'label' => 'Discount Label for 10% off'
                            ]
                        ],
                        'bundle_options' => [
                            [
                                'label' => 'Drop Down Option 1',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product1',
                                        'product_sku' => 'simple1',
                                        'quantity' => 1,
                                        'price' => ['value' => 1, 'currency' => 'USD']
                                    ]
                                ]
                            ],
                            [
                                'label' => 'Drop Down Option 2',
                                'values' => [
                                    [
                                        'product_name' => 'Simple Product2',
                                        'product_sku' => 'simple2',
                                        'quantity' => 2,
                                        'price' => ['value' => 2, 'currency' => 'USD']
                                    ]
                                ]
                            ]
                        ],
                        'quantity_refunded' => 1
                    ],

                ],
                'total' => [
                    'subtotal' => [
                        'value' => 15
                    ],
                    'grand_total' => [
                        'value' => 24.19,
                        'currency' => 'USD'
                    ],
                    'base_grand_total' => [
                        'value' => 24.19,
                        'currency' => 'USD'
                    ],
                    'total_shipping' => [
                        'value' => 10
                    ],
                    'total_tax' => [
                        'value'=> 1.69
                    ],
                    'shipping_handling' => [
                        'amount_including_tax' => [
                            'value' => 10.75
                        ],
                        'amount_excluding_tax' => [
                            'value' => 10
                        ],
                        'total_amount' => [
                            'value' => 10
                        ],
                        'taxes'=> [
                            0 => [
                                'amount' => ['value' => 0.67],
                                'title' => 'US-TEST-*-Rate-1',
                                'rate' => 7.5
                            ]
                        ],
                        'discounts' => [
                            [
                                'amount'=> ['value'=> 1]
                            ]
                        ],
                    ],
                    'adjustment' => [
                        'value' => 0
                    ]
                ]
            ]
        ];
        $firstOrderItem = current($response['customer']['orders']['items'] ?? []);
        $this->assertArrayHasKey('credit_memos', $firstOrderItem);

        $creditMemos = $firstOrderItem['credit_memos'];
        $this->assertResponseFields($creditMemos, $expectedCreditMemoData);
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
     * @return void
     */
    private function deleteOrder(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $order \Magento\Sales\Model\Order */
        $orderCollection = Bootstrap::getObjectManager()->create(OrderCollection::class);
        foreach ($orderCollection as $order) {
            $this->orderRepository->delete($order);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * @return void
     */
    private function cleanUpCreditMemos(): void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $creditmemoRepository = Bootstrap::getObjectManager()->get(CreditmemoRepositoryInterface::class);
        $creditmemoCollection = Bootstrap::getObjectManager()->create(Collection::class);
        foreach ($creditmemoCollection as $creditmemo) {
            $creditmemoRepository->delete($creditmemo);
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     *  Get CustomerOrder with credit memo details
     *
     * @return array
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function getCustomerOrderWithCreditMemoQuery(): array
    {
        $query =
            <<<QUERY
query {
  customer {
    orders {
        items {
            invoices {
               items {
                    product_name
                    product_sku
                    product_sale_price {
                        value
                    }
                    ... on BundleInvoiceItem {
                      bundle_options {
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
                    discounts { amount{value currency} label }
                    quantity_invoiced
                    discounts { amount{value currency} label }
               }
            }
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
                    ... on BundleCreditMemoItem {
                      bundle_options {
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
                    discounts { amount{value currency} label }
                    quantity_refunded
                }
                total {
                    subtotal {
                        value
                    }
                    base_grand_total  {
                        value
                        currency
                    }
                    grand_total {
                        value
                        currency
                    }
                    total_shipping {
                        value
                    }
                    total_tax {
                        value
                    }
                    shipping_handling {
                         amount_including_tax{value}
                         amount_excluding_tax{value}
                         total_amount{value}
                         taxes {amount{value} title rate}
                         discounts {amount{value}}
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
        return $response;
    }
}
