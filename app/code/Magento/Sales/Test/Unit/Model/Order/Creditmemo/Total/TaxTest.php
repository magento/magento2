<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Framework\DataObject as MagentoObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\Total\Tax;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Collecting credit memo taxes
 */
class TaxTest extends TestCase
{
    /**
     * @var Tax
     */
    protected $model;

    /**
     * @var Order|MockObject
     */
    protected $order;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemo;

    /**
     * @var Creditmemo|MockObject
     */
    protected $invoice;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        /** @var Tax $model */
        $this->model = $this->objectManager->getObject(Tax::class);

        $this->order = $this->createPartialMock(Order::class, ['__wakeup']);
        $this->invoice = $this->createPartialMock(Invoice::class, ['__wakeup']);

        $this->creditmemo = $this->createPartialMock(
            Creditmemo::class,
            [
                'getAllItems',
                'getOrder',
                'roundPrice',
                'isLast',
            ]
        );
        $this->creditmemo->expects($this->atLeastOnce())->method('getOrder')->willReturn($this->order);
    }

    /**
     * @param array $orderData
     * @param array $creditmemoData
     * @param array $expectedResults
     * @dataProvider collectDataProvider
     */
    public function testCollect($orderData, $creditmemoData, $expectedResults)
    {
        $roundingDelta = [];

        //Set up order mock
        foreach ($orderData['data_fields'] as $key => $value) {
            $this->order->setData($key, $value);
        }

        //Set up creditmemo mock
        /** @var Item[] $creditmemoItems */
        $creditmemoItems = [];
        foreach ($creditmemoData['items'] as $itemKey => $creditmemoItemData) {
            $creditmemoItems[$itemKey] = $this->getCreditmemoItem($creditmemoItemData);
        }
        $this->creditmemo->expects($this->once())
            ->method('getAllItems')
            ->willReturn($creditmemoItems);
        $this->creditmemo->expects($this->any())
            ->method('isLast')
            ->willReturn($creditmemoData['is_last']);
        foreach ($creditmemoData['data_fields'] as $key => $value) {
            $this->creditmemo->setData($key, $value);
        }
        $this->creditmemo->expects($this->any())
            ->method('roundPrice')
            ->willReturnCallback(
                function ($price, $type) use (&$roundingDelta) {
                    if (!isset($roundingDelta[$type])) {
                        $roundingDelta[$type] = 0;
                    }
                    $roundedPrice = round($price + $roundingDelta[$type], 2);
                    $roundingDelta[$type] = $price - $roundedPrice;
                    return $roundedPrice;
                }
            );

        $this->model->collect($this->creditmemo);

        //verify invoice data
        foreach ($expectedResults['creditmemo_data'] as $key => $value) {
            $this->assertEquals($value, $this->creditmemo->getData($key));
        }
        //verify invoice item data
        foreach ($expectedResults['creditmemo_items'] as $itemKey => $itemData) {
            $creditmemoItem = $creditmemoItems[$itemKey];
            foreach ($itemData as $key => $value) {
                $this->assertEquals($value, $creditmemoItem->getData($key));
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function collectDataProvider()
    {
        $result = [];
        // scenario 1: 3 item_1, 3 item_2, $99 each, 8.19 tax rate
        // 1 item_1 and 2 item_2 are invoiced
        $result['partial_invoice_partial_creditmemo'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 2.45,
                    'base_shipping_tax_amount' => 2.45,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 53.56,
                    'base_tax_amount' => 53.56,
                    'tax_invoiced' => 24.33,
                    'base_tax_invoiced' => 24.33,
                    'tax_refunded' => 0,
                    'base_tax_refunded' => 0,
                    'base_shipping_amount' => 30,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 1,
                            'tax_invoiced' => 8.11,
                            'tax_refunded' => 0,
                            'base_tax_invoiced' => 8.11,
                            'base_tax_refunded' => 0,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_invoiced' => 2,
                            'tax_refunded' => 0,
                            'tax_invoiced' => 16.22,
                            'base_tax_refunded' => 0,
                            'base_tax_invoiced' => 16.22,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 198,
                    'base_grand_total' => 198,
                    'base_shipping_amount' => 30,
                    'tax_amount' => 0.82,
                    'base_tax_amount' => 0.82,
                    'invoice' => $this->createInvoiceMock(
                        [
                            'tax_amount' => 24.33,
                            'base_tax_amount' => 24.33,
                            'shipping_tax_amount' => 2.45,
                            'base_shipping_tax_amount' => 2.45,
                            'shipping_discount_tax_compensation_amount' => 0,
                            'base_shipping_discount_tax_compensation_amount' => 0,
                        ]
                    ),
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 8.11,
                        'base_tax_amount' => 8.11,
                    ],
                    'item_2' => [
                        'tax_amount' => 8.11,
                        'base_tax_amount' => 8.11,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 216.67,
                    'base_grand_total' => 216.67,
                    'tax_amount' => 19.49,
                    'base_tax_amount' => 19.49,
                    'shipping_tax_amount' => 2.45,
                    'base_shipping_tax_amount' => 2.45,
                ],
            ],
        ];

        $currencyRatio = 2;
        // scenario 1: 3 item_1, 3 item_2, $99 each, 8.19 tax rate
        // 1 item_1 and 2 item_2 are invoiced and base currency <> display currency
        $result['partial_invoice_partial_creditmemo_different_currencies'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 2.45 * $currencyRatio,
                    'base_shipping_tax_amount' => 2.45,
                    'shipping_discount_tax_compensation_amount' => 0.00,
                    'base_shipping_discount_tax_compensation_amount' => 0.00,
                    'tax_amount' => 53.56 * $currencyRatio,
                    'base_tax_amount' => 53.56,
                    'tax_invoiced' => 24.33 * $currencyRatio,
                    'base_tax_invoiced' => 24.33,
                    'tax_refunded' => 0.00,
                    'base_tax_refunded' => 0.00,
                    'base_shipping_amount' => 30.00,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 1,
                            'tax_invoiced' => 8.11 * $currencyRatio,
                            'tax_refunded' => 0,
                            'base_tax_invoiced' => 8.11,
                            'base_tax_refunded' => 0,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_invoiced' => 2,
                            'tax_refunded' => 0,
                            'tax_invoiced' => 16.22 * $currencyRatio,
                            'base_tax_refunded' => 0,
                            'base_tax_invoiced' => 16.22,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 198 * $currencyRatio,
                    'base_grand_total' => 198,
                    'base_shipping_amount' => 30,
                    'tax_amount' => 0.82 * $currencyRatio,
                    'base_tax_amount' => 0.82,
                    'invoice' => $this->createInvoiceMock(
                        [
                            'tax_amount' => 24.33 * $currencyRatio,
                            'base_tax_amount' => 24.33,
                            'shipping_tax_amount' => 2.45 * $currencyRatio,
                            'base_shipping_tax_amount' => 2.45,
                            'shipping_discount_tax_compensation_amount' => 0,
                            'base_shipping_discount_tax_compensation_amount' => 0,
                        ]
                    ),
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 8.11 * $currencyRatio,
                        'base_tax_amount' => 8.11,
                    ],
                    'item_2' => [
                        'tax_amount' => 8.11 * $currencyRatio,
                        'base_tax_amount' => 8.11,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 216.67 * $currencyRatio,
                    'base_grand_total' => 216.67,
                    'tax_amount' => 19.49 * $currencyRatio,
                    'base_tax_amount' => 19.49,
                    'shipping_tax_amount' => 2.45 * $currencyRatio,
                    'base_shipping_tax_amount' => 2.45,
                ],
            ],
        ];

        // scenario 2: 3 items, 2 invoiced, rowtotal of 150 with 8.25 tax rate
        // extra tax amount exist (weee tax), make sure that tax amount
        // is not over the amount invoiced
        $result['tax_amount_not_over_invoiced_tax_amount'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                    'tax_invoiced' => 11.14,
                    'base_tax_invoiced' => 11.14,
                    'tax_refunded' => 0,
                    'base_tax_refunded' => 0,
                    'base_shipping_amount' => 30,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 2,
                            'tax_invoiced' => 8.26,
                            'tax_refunded' => 0,
                            'base_tax_invoiced' => 8.26,
                            'base_tax_refunded' => 0,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 2,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 136.65,
                    'base_grand_total' => 136.65,
                    'base_shipping_amount' => 30,
                    'tax_amount' => 1.65,
                    'base_tax_amount' => 1.65,
                    'invoice' => $this->createInvoiceMock(
                        [
                            'tax_amount' => 11.14,
                            'base_tax_amount' => 11.14,
                            'shipping_tax_amount' => 1.24,
                            'base_shipping_tax_amount' => 1.24,
                            'shipping_discount_tax_compensation_amount' => 0,
                            'base_shipping_discount_tax_compensation_amount' => 0,
                        ]
                    ),
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 8.26,
                        'base_tax_amount' => 8.26,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 146.14,
                    'base_grand_total' => 146.14,
                    'tax_amount' => 11.14,
                    'base_tax_amount' => 11.14,
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                ],
            ],
        ];

        // scenario 3: 3 items, 3 invoiced, rowtotal of 150 with 8.25 tax rate
        // extra tax amount exist (weee tax), make sure that tax amount
        // equals to tax amount invoiced
        $result['last_partial_creditmemo'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_tax_refunded' => 1.24,
                    'base_shipping_tax_refunded' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                    'tax_invoiced' => 16.09,
                    'base_tax_invoiced' => 16.09,
                    'tax_refunded' => 11.14,
                    'base_tax_refunded' => 11.14,
                    'shipping_amount' => 15,
                    'shipping_amount_refunded' => 15,
                    'base_shipping_amount' => 15,
                    'base_shipping_amount_refunded' => 15,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 3,
                            'tax_invoiced' => 12.38,
                            'tax_refunded' => 8.26,
                            'base_tax_invoiced' => 12.38,
                            'base_tax_refunded' => 8.26,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 2,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                ],
                'is_last' => true,
                'data_fields' => [
                    'grand_total' => 60.82,
                    'base_grand_total' => 60.82,
                    'base_shipping_amount' => 0,
                    'tax_amount' => 0.82,
                    'base_tax_amount' => 0.82,
                    'invoice' => $this->createInvoiceMock(
                        [
                            'tax_amount' => 16.09,
                            'base_tax_amount' => 16.09,
                            'shipping_tax_amount' => 1.24,
                            'base_shipping_tax_amount' => 1.24,
                            'shipping_discount_tax_compensation_amount' => 0,
                            'base_shipping_discount_tax_compensation_amount' => 0,
                        ]
                    ),
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 4.12,
                        'base_tax_amount' => 4.12,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 64.94,
                    'base_grand_total' => 64.94,
                    'tax_amount' => 4.94,
                    'base_tax_amount' => 4.94,
                ],
            ],
        ];

        // scenario 4: 3 items, 2 invoiced, price includes tax
        // partial credit memo, make sure that discount tax compensation is calculated correctly
        $result['partial_invoice_partial_creditmemo_price_incl_tax'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 0,
                    'base_shipping_tax_amount' => 0,
                    'shipping_tax_refunded' => 0,
                    'base_shipping_tax_refunded' => 0,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 13.72,
                    'base_tax_amount' => 13.72,
                    'tax_invoiced' => 9.15,
                    'base_tax_invoiced' => 9.15,
                    'tax_refunded' => 0,
                    'base_tax_refunded' => 0,
                    'discount_tax_compensation_amount' => 11.43,
                    'base_discount_tax_compensation_amount' => 11.43,
                    'discount_tax_compensation_invoiced' => 7.62,
                    'base_discount_tax_compensation_invoiced' => 7.62,
                    'discount_tax_compensation_refunded' => 0,
                    'base_discount_tax_compensation_refunded' => 0,
                    'shipping_amount' => 0,
                    'shipping_amount_refunded' => 0,
                    'base_shipping_amount' => 0,
                    'base_shipping_amount_refunded' => 0,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 2,
                            'tax_invoiced' => 7.62,
                            'tax_refunded' => 0,
                            'base_tax_invoiced' => 7.62,
                            'base_tax_refunded' => 0,
                            'discount_tax_compensation_amount' => 11.43,
                            'base_discount_tax_compensation_amount' => 11.43,
                            'discount_tax_compensation_invoiced' => 7.62,
                            'base_discount_tax_compensation_invoiced' => 7.62,
                            'qty_refunded' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 52.38,
                    'base_grand_total' => 52.38,
                    'base_shipping_amount' => 0,
                    'tax_amount' => 0.76,
                    'base_tax_amount' => 0.76,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 3.81,
                        'base_tax_amount' => 3.81,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 60,
                    'base_grand_total' => 60,
                    'tax_amount' => 4.57,
                    'base_tax_amount' => 4.57,
                ],
            ],
        ];

        // scenario 5: 3 items, 3 invoiced, rowtotal of 150 with 8.25 tax rate
        // shipping is partially returned
        $result['last_partial_creditmemo_with_partial_shipping_refund'] = [
            'order_data' => [
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_tax_refunded' => 0,
                    'base_shipping_tax_refunded' => 0,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                    'tax_invoiced' => 16.09,
                    'base_tax_invoiced' => 16.09,
                    'tax_refunded' => 9.9,
                    'base_tax_refunded' => 9.9,
                    'shipping_amount' => 15,
                    'shipping_amount_refunded' => 0,
                    'base_shipping_amount' => 15,
                    'base_shipping_amount_refunded' => 0,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 3,
                            'tax_invoiced' => 12.38,
                            'tax_refunded' => 8.26,
                            'base_tax_invoiced' => 12.38,
                            'base_tax_refunded' => 8.26,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_refunded' => 2,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                ],
                'is_last' => true,
                'data_fields' => [
                    'shipping_amount' => 7.5,
                    'base_shipping_amount' => 7.5,
                    'grand_total' => 60.82,
                    'base_grand_total' => 60.82,
                    'tax_amount' => 0.82,
                    'base_tax_amount' => 0.82,
                    'invoice' => $this->createInvoiceMock(
                        [
                            'tax_amount' => 16.09,
                            'base_tax_amount' => 16.09,
                            'shipping_tax_amount' => 1.24,
                            'base_shipping_tax_amount' => 1.24,
                            'shipping_discount_tax_compensation_amount' => 0,
                            'base_shipping_discount_tax_compensation_amount' => 0,
                        ]
                    ),
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 4.12,
                        'base_tax_amount' => 4.12,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 65.56,
                    'base_grand_total' => 65.56,
                    'tax_amount' => 5.56,
                    'base_tax_amount' => 5.56,
                ],
            ],
        ];

        // scenario 6: 2 items, 2 invoiced, price includes tax, full discount, free shipping
        // partial credit memo, make sure that discount tax compensation (with 100 % discount) is calculated correctly
        $result['collect_with_full_discount_product_price'] = [
            'order_data' => [
                'data_fields' => [
                    'discount_amount' => -200.00,
                    'discount_invoiced' => -200.00,
                    'subtotal' => 181.82,
                    'subtotal_incl_tax' => 200,
                    'base_subtotal' => 181.82,
                    'base_subtotal_incl_tax' => 200,
                    'subtotal_invoiced' => 181.82,
                    'discount_tax_compensation_amount' => 18.18,
                    'discount_tax_compensation_invoiced' => 18.18,
                    'base_discount_tax_compensation_amount' => 18.18,
                    'base_discount_tax_compensation_invoiced' => 18.18,
                    'grand_total' => 0,
                    'base_grand_total' => 0,
                    'shipping_tax_amount' => 0,
                    'base_shipping_tax_amount' => 0,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                    'tax_invoiced' => 0,
                    'base_tax_invoiced' => 0,
                    'tax_refunded' => 0,
                    'base_tax_refunded' => 0,
                    'base_shipping_amount' => 0,
                ],
            ],
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_invoiced' => 1,
                            'tax_amount' => 0,
                            'tax_invoiced' => 0,
                            'tax_refunded' => null,
                            'base_tax_amount' => 0,
                            'base_tax_invoiced' => 0,
                            'base_tax_refunded' => 0,
                            'tax_percent' => 10,
                            'qty_refunded' => 0,
                            'discount_percent' => 100,
                            'discount_amount' => 100,
                            'base_discount_amount' => 100,
                            'discount_invoiced' => 100,
                            'base_discount_invoiced' => 100,
                            'row_total' => 90.91,
                            'base_row_total' => 90.91,
                            'row_invoiced' => 90.91,
                            'base_row_invoiced' => 90.91,
                            'price_incl_tax' => 100,
                            'base_price_incl_tax' => 100,
                            'row_total_incl_tax' => 100,
                            'base_row_total_incl_tax' => 100,
                            'discount_tax_compensation_amount' => 9.09,
                            'base_discount_tax_compensation_amount' => 9.09,
                            'discount_tax_compensation_invoiced' => 9.09,
                            'base_discount_tax_compensation_invoiced' => 9.09,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_invoiced' => 1,
                            'tax_amount' => 0,
                            'tax_invoiced' => 0,
                            'tax_refunded' => null,
                            'base_tax_amount' => 0,
                            'base_tax_invoiced' => 0,
                            'base_tax_refunded' => null,
                            'tax_percent' => 10,
                            'qty_refunded' => 0,
                            'discount_percent' => 100,
                            'discount_amount' => 100,
                            'base_discount_amount' => 100,
                            'discount_invoiced' => 100,
                            'base_discount_invoiced' => 100,
                            'row_total' => 90.91,
                            'base_row_total' => 90.91,
                            'row_invoiced' => 90.91,
                            'base_row_invoiced' => 90.91,
                            'price_incl_tax' => 100,
                            'base_price_incl_tax' => 100,
                            'row_total_incl_tax' => 100,
                            'base_row_total_incl_tax' => 100,
                            'discount_tax_compensation_amount' => 9.09,
                            'base_discount_tax_compensation_amount' => 9.09,
                            'discount_tax_compensation_invoiced' => 9.09,
                            'base_discount_tax_compensation_invoiced' => 9.09,
                        ],
                        'is_last' => false,
                        'qty' => 0,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => -9.09,
                    'base_grand_total' => -9.09,
                    'base_shipping_amount' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
                    'item_1' => [
                        'tax_amount' => 0,
                        'base_tax_amount' => 0,
                    ],
                    'item_2' => [
                        'tax_amount' => 0,
                        'base_tax_amount' => 0,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 0,
                    'base_grand_total' => 0,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                    'shipping_tax_amount' => 0,
                    'base_shipping_tax_amount' => 0,
                ],
            ],
        ];

        return $result;
    }

    /**
     * @param $creditmemoItemData array
     * @return Item|MockObject
     */
    protected function getCreditmemoItem($creditmemoItemData)
    {
        /** @var \Magento\Sales\Model\Order\Item|MockObject $orderItem */
        $orderItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            [
                'isDummy'
            ]
        );
        foreach ($creditmemoItemData['order_item'] as $key => $value) {
            $orderItem->setData($key, $value);
        }

        /** @var Item|MockObject $creditmemoItem */
        $creditmemoItem = $this->createPartialMock(
            Item::class,
            [
                'getOrderItem',
                'isLast'
            ]
        );
        $creditmemoItem->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $creditmemoItem->expects($this->any())
            ->method('isLast')
            ->willReturn($creditmemoItemData['is_last']);
        $creditmemoItem->setData('qty', $creditmemoItemData['qty']);
        return $creditmemoItem;
    }

    /**
     * Create invoice mock object
     *
     * @param array $data
     * @return MockObject|Invoice
     */
    private function createInvoiceMock(array $data): MockObject
    {
        /** @var MockObject|Invoice $invoice */
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['getBaseShippingDiscountTaxCompensationAmount'])
            ->onlyMethods([
                'getTaxAmount',
                'getBaseTaxAmount',
                'getShippingTaxAmount',
                'getBaseShippingTaxAmount',
                'getShippingDiscountTaxCompensationAmount'
            ])
            ->getMock();

        $invoice->method('getTaxAmount')->willReturn($data['tax_amount'] ?? 0);
        $invoice->method('getBaseTaxAmount')->willReturn($data['base_tax_amount'] ?? 0);
        $invoice->method('getShippingTaxAmount')->willReturn($data['shipping_tax_amount'] ?? 0);
        $invoice->method('getBaseShippingTaxAmount')->willReturn($data['base_shipping_tax_amount'] ?? 0);
        $invoice->method('getShippingDiscountTaxCompensationAmount')
            ->willReturn($data['shipping_discount_tax_compensation_amount'] ?? 0);
        $invoice->method('getBaseShippingDiscountTaxCompensationAmount')
            ->willReturn($data['base_shipping_discount_tax_compensation_amount'] ?? 0);

        return $invoice;
    }
}
