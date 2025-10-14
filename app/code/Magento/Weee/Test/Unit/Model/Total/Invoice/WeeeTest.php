<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Model\Total\Invoice;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item;
use Magento\Sales\Model\Order\Invoice\Total\Tax;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\Total\Invoice\Weee;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WeeeTest extends TestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /**
     * @var Weee
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
     * @var Invoice|MockObject
     */
    protected $invoice;

    /**
     * @var Data|MockObject
     */
    protected $weeeData;

    protected function setUp(): void
    {
        $this->weeeData = $this->getMockBuilder(Data::class)
            ->onlyMethods(
                [
                    'getRowWeeeTaxInclTax',
                    'getBaseRowWeeeTaxInclTax',
                    'getWeeeAmountInvoiced',
                    'getBaseWeeeAmountInvoiced',
                    'getWeeeTaxAmountInvoiced',
                    'getBaseWeeeTaxAmountInvoiced',
                    'getApplied',
                    'setApplied',
                    'includeInSubtotal',
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $serializer = $this->objectManager->getObject(Json::class);
        /** @var Tax $model */
        $this->model = $this->objectManager->getObject(
            Weee::class,
            [
                'weeeData' => $this->weeeData,
                'serializer' => $serializer
            ]
        );

        $this->order = $this->createPartialMock(Order::class, ['__wakeup']);

        $this->invoice = $this->createPartialMock(Invoice::class, [
            'getAllItems',
            'getOrder',
            'roundPrice',
            'isLast',
            'getStore'
        ]);
        $this->invoice->expects($this->atLeastOnce())->method('getOrder')->willReturn($this->order);
    }

    /**
     * @param $orderData
     */
    private function setupOrder($orderData)
    {
        //Set up order mock
        foreach ($orderData['data_fields'] as $key => $value) {
            $this->order->setData($key, $value);
        }
    }

    /**
     * @param array $orderData
     * @param array $invoiceData
     * @param array $expectedResults
     * @dataProvider collectDataProvider
     */
    public function testCollect($orderData, $invoiceData, $expectedResults)
    {
        $roundingDelta = [];

        $this->setupOrder($orderData);

        //Set up weeeData mock
        $this->weeeData->expects($this->atLeastOnce())
            ->method('includeInSubtotal')
            ->willReturn($invoiceData['include_in_subtotal']);

        //Set up invoice mock
        /** @var Item[] $invoiceItems */
        $invoiceItems = [];
        foreach ($invoiceData['items'] as $itemKey => $invoiceItemData) {
            $invoiceItems[$itemKey] = $this->getInvoiceItem($invoiceItemData);
        }
        $this->invoice->expects($this->once())
            ->method('getAllItems')
            ->willReturn($invoiceItems);
        $this->invoice->expects($this->once())
            ->method('isLast')
            ->willReturn($invoiceData['is_last']);
        foreach ($invoiceData['data_fields'] as $key => $value) {
            $this->invoice->setData($key, $value);
        }
        $this->invoice->expects($this->any())
            ->method('roundPrice')
            ->willReturnCallback(function ($price, $type) use (&$roundingDelta) {
                if (!isset($roundingDelta[$type])) {
                    $roundingDelta[$type] = 0;
                }
                $roundedPrice = round($price + $roundingDelta[$type], 2);
                $roundingDelta[$type] = $price - $roundedPrice;

                return $roundedPrice;
            });

        $this->model->collect($this->invoice);

        //verify invoice data
        foreach ($expectedResults['invoice_data'] as $key => $value) {
            $this->assertEqualsWithDelta(
                $value,
                $this->invoice->getData($key),
                self::EPSILON,
                'Invoice data field ' . $key . ' is incorrect'
            );
        }
        //verify invoice item data
        foreach ($expectedResults['invoice_items'] as $itemKey => $itemData) {
            $invoiceItem = $invoiceItems[$itemKey];
            foreach ($itemData as $key => $value) {
                if ($key == 'tax_ratio') {
                    $taxRatio = json_decode($invoiceItem->getData($key), true);
                    $this->assertEqualsWithDelta(
                        $value['weee'],
                        $taxRatio['weee'],
                        self::EPSILON,
                        "Tax ratio is incorrect"
                    );
                } else {
                    $this->assertEqualsWithDelta(
                        $value,
                        $invoiceItem->getData($key),
                        self::EPSILON,
                        'Invoice item field ' . $key . ' is incorrect'
                    );
                }
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function collectDataProvider()
    {
        $result = [];

        // 3 item_1, $100 with $weee, 8.25 tax rate, full invoice
        $result['complete_invoice'] = [
            'orderData' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'tax_invoiced' => 0,
                    'base_tax_amount' => 16.09,
                    'base_tax_amount_invoiced' => 0,
                    'subtotal' => '300',
                    'base_subtotal' => '300',
                ],
            ],
            'invoiceData' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 0,
                            'base_weee_amount_invoiced' => 0,
                            'weee_tax_amount_invoiced' => 0,
                            'base_weee_tax_amount_invoiced' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'applied_weee_updated' => [
                                'base_row_amount_invoiced' => 30,
                                'row_amount_invoiced' => 30,
                                'base_tax_amount_invoiced' => 2.47,
                                'tax_amount_invoiced' => 2.47,
                            ],
                            'qty_invoiced' => 0,
                        ],
                        'is_last' => true,
                        'data_fields' => [
                            'qty' => 3,
                            'applied_weee' => [
                                [
                                ],
                            ],
                        ],
                    ],
                ],
                'is_last' => true,
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 181.09,
                    'base_grand_total' => 181.09,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 314.85,
                    'base_subtotal_incl_tax' => 314.85,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                ],
            ],
            'expectedResults' => [
                'invoice_items' => [
                    'item_1' => [
                        'applied_weee' => [
                            [
                                'title' => 'recycling_fee',
                                'base_row_amount' => 30,
                                'row_amount' => 30,
                                'base_row_amount_incl_tax' => 32.47,
                                'row_amount_incl_tax' => 32.47,
                            ],
                        ],
                        'weee_tax_applied_row_amount' => 30,
                        'base_weee_tax_applied_row_amount' => 30,
                        'tax_ratio' => ["weee" => 1.0],
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 211.09,
                    'base_grand_total' => 211.09,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 347.32,
                    'base_subtotal_incl_tax' => 347.32,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate, partial invoice, invoice qty=2
        $result['partial_invoice'] = [
            'orderData' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'tax_invoiced' => 0,
                    'base_tax_amount' => 16.09,
                    'base_tax_amount_invoiced' => 0,
                    'subtotal' => '300',
                    'base_subtotal' => '300',
                ],
            ],
            'invoiceData' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 0,
                            'base_weee_amount_invoiced' => 0,
                            'weee_tax_amount_invoiced' => 0,
                            'base_weee_tax_amount_invoiced' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'applied_weee_updated' => [
                                'base_row_amount_invoiced' => 30,
                                'row_amount_invoiced' => 30,
                                'base_tax_amount_invoiced' => 2.47,
                                'tax_amount_invoiced' => 2.47,
                            ],
                            'qty_invoiced' => 0,
                        ],
                        'is_last' => false,
                        'data_fields' => [
                            'qty' => 2,
                            'applied_weee' => [
                                [
                                ],
                            ],
                        ],
                    ],
                ],
                'is_last' => false,
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 124.49,
                    'base_grand_total' => 124.49,
                    'subtotal' => 200,
                    'base_subtotal' => 200,
                    'subtotal_incl_tax' => 216.5,
                    'base_subtotal_incl_tax' => 216.5,
                    'tax_amount' => 9.49,
                    'base_tax_amount' => 9.49,
                ],
            ],
            'expectedResults' => [
                'invoice_items' => [
                    'item_1' => [
                        'applied_weee' => [
                            [
                                'title' => 'recycling_fee',
                                'base_row_amount' => 20,
                                'row_amount' => 20,
                                'base_row_amount_incl_tax' => 21.65,
                                'row_amount_incl_tax' => 21.65,
                            ],
                        ],
                        'tax_ratio' => ['weee' => 1.65 / 2.47],
                        'weee_tax_applied_row_amount' => 20,
                        'base_weee_tax_applied_row_amount' => 20,
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 146.14,
                    'base_grand_total' => 146.14,
                    'tax_amount' => 11.14,
                    'base_tax_amount' => 11.14,
                    'subtotal' => 200,
                    'base_subtotal' => 200,
                    'subtotal_incl_tax' => 238.15,
                    'base_subtotal_incl_tax' => 238.15,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate, partial invoice: one item invoiced
        // invoice another item
        $result['second_partial_invoice'] = [
            'orderData' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'tax_invoiced' => 0,
                    'base_tax_amount' => 16.09,
                    'base_tax_amount_invoiced' => 0,
                    'subtotal' => '300',
                    'base_subtotal' => '300',
                ],
            ],
            'invoiceData' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 0,
                            'base_weee_amount_invoiced' => 0,
                            'weee_tax_amount_invoiced' => 0,
                            'base_weee_tax_amount_invoiced' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'applied_weee_updated' => [
                                'base_row_amount_invoiced' => 30,
                                'row_amount_invoiced' => 30,
                                'base_tax_amount_invoiced' => 2.47,
                                'tax_amount_invoiced' => 2.47,
                            ],
                            'qty_invoiced' => 1,
                        ],
                        'is_last' => false,
                        'data_fields' => [
                            'qty' => 1,
                            'applied_weee' => [
                                [
                                ],
                            ],
                        ],
                    ],
                ],
                'is_last' => false,
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 54.13,
                    'base_grand_total' => 54.13,
                    'tax_amount' => 4.13,
                    'base_tax_amount' => 4.13,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 108.25,
                    'base_subtotal_incl_tax' => 108.25,
                ],
            ],
            'expectedResults' => [
                'invoice_items' => [
                    'item_1' => [
                        'applied_weee' => [
                            [
                                'title' => 'recycling_fee',
                                'base_row_amount' => 10,
                                'row_amount' => 10,
                                'base_row_amount_incl_tax' => 10.82,
                                'row_amount_incl_tax' => 10.82,
                            ],
                        ],
                        'tax_ratio' => ['weee' => 0.82 / 2.47],
                        'weee_tax_applied_row_amount' => 10,
                        'base_weee_tax_applied_row_amount' => 10,
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 64.95,
                    'base_grand_total' => 64.95,
                    'tax_amount' => 4.95,
                    'base_tax_amount' => 4.95,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 119.07,
                    'base_subtotal_incl_tax' => 119.07,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate, partial invoice: two item invoiced
        // invoice another item
        $result['last_partial_invoice'] = [
            'orderData' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'tax_invoiced' => 11.14,
                    'base_tax_amount' => 16.09,
                    'base_tax_invoiced' => 11.14,
                    'subtotal' => '300',
                    'base_subtotal' => '300',
                ],
            ],
            'invoiceData' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 20,
                            'base_weee_amount_invoiced' => 20,
                            'weee_tax_amount_invoiced' => 1.64,
                            'base_weee_tax_amount_invoiced' => 1.64,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'applied_weee_updated' => [
                                'base_row_amount_invoiced' => 30,
                                'row_amount_invoiced' => 30,
                                'base_tax_amount_invoiced' => 2.47,
                                'tax_amount_invoiced' => 2.47,
                            ],
                            'qty_invoiced' => 2,
                        ],
                        'is_last' => true,
                        'data_fields' => [
                            'qty' => 1,
                            'applied_weee' => [
                                [
                                ],
                            ],
                        ],
                    ],
                ],
                'is_last' => true,
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 54.95,
                    'base_grand_total' => 54.95,
                    'tax_amount' => 4.95,
                    'base_tax_amount' => 4.95,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 104.95,
                    'base_subtotal_incl_tax' => 104.95,
                ],
            ],
            'expectedResults' => [
                'invoice_items' => [
                    'item_1' => [
                        'applied_weee' => [
                            [
                                'title' => 'recycling_fee',
                                'base_row_amount' => 10,
                                'row_amount' => 10,
                                'base_row_amount_incl_tax' => 10.82,
                                'row_amount_incl_tax' => 10.82,
                            ],
                        ],
                        'tax_ratio' => ['weee' => 0.83 / 2.47],
                        'weee_tax_applied_row_amount' => 10,
                        'base_weee_tax_applied_row_amount' => 10,

                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 64.95,
                    'base_grand_total' => 64.95,
                    'tax_amount' => 4.95,
                    'base_tax_amount' => 4.95,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 115.77,
                    'base_subtotal_incl_tax' => 115.77,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate. Invoicing qty 0.
        $result['zero_invoice'] = [
            'orderData' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 1.24,
                    'base_shipping_tax_amount' => 1.24,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 16.09,
                    'tax_invoiced' => 0,
                    'base_tax_amount' => 16.09,
                    'base_tax_amount_invoiced' => 0,
                    'subtotal' => '300',
                    'base_subtotal' => '300',
                ],
            ],
            'invoiceData' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 0,
                            'base_weee_amount_invoiced' => 0,
                            'weee_tax_amount_invoiced' => 0,
                            'base_weee_tax_amount_invoiced' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'applied_weee_updated' => [
                                'base_row_amount_invoiced' => 30,
                                'row_amount_invoiced' => 30,
                                'base_tax_amount_invoiced' => 2.47,
                                'tax_amount_invoiced' => 2.47,
                            ],
                            'qty_invoiced' => 0,
                        ],
                        'is_last' => true,
                        'data_fields' => [
                            'qty' => 0,
                            'applied_weee' => [
                                [
                                ],
                            ],
                        ],
                    ],
                ],
                'is_last' => true,
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 181.09,
                    'base_grand_total' => 181.09,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 314.85,
                    'base_subtotal_incl_tax' => 314.85,
                    'tax_amount' => 16.09,
                    'base_tax_amount' => 16.09,
                ],
            ],
            'expectedResults' => [
                'invoice_items' => [
                    'item_1' => [
                        'applied_weee' => [
                            [
                                'title' => 'recycling_fee',
                                'base_row_amount' => 0,
                                'row_amount' => 0,
                                'base_row_amount_incl_tax' => 0,
                                'row_amount_incl_tax' => 0,
                            ],
                        ],
                    ],
                ],
                'invoice_data' => [
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                ],
            ],
        ];

        return $result;
    }

    /**
     * @param $invoiceItemData array
     * @return Item|MockObject
     */
    protected function getInvoiceItem($invoiceItemData)
    {
        /** @var \Magento\Sales\Model\Order\Item|MockObject $orderItem */
        $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, [
            'isDummy'
        ]);
        foreach ($invoiceItemData['order_item'] as $key => $value) {
            $orderItem->setData($key, $value);
        }

        $this->weeeData->expects($this->once())
            ->method('getRowWeeeTaxInclTax')
            ->with($orderItem)
            ->willReturn($orderItem->getRowWeeeTaxInclTax());
        $this->weeeData->expects($this->once())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($orderItem)
            ->willReturn($orderItem->getBaseRowWeeeTaxInclTax());
        if ($invoiceItemData['is_last']) {
            $this->weeeData->expects($this->once())
                ->method('getWeeeAmountInvoiced')
                ->with($orderItem)
                ->willReturn($orderItem->getWeeeAmountInvoiced());
            $this->weeeData->expects($this->once())
                ->method('getBaseWeeeAmountInvoiced')
                ->with($orderItem)
                ->willReturn($orderItem->getBaseWeeeAmountInvoiced());
            $this->weeeData->expects($this->once())
                ->method('getWeeeTaxAmountInvoiced')
                ->with($orderItem)
                ->willReturn($orderItem->getWeeeTaxAmountInvoiced());
            $this->weeeData->expects($this->once())
                ->method('getBaseWeeeTaxAmountInvoiced')
                ->with($orderItem)
                ->willReturn($orderItem->getBaseWeeeTaxAmountInvoiced());
        }
        /** @var Item|MockObject $invoiceItem */
        $invoiceItem = $this->createPartialMock(Item::class, [
            'getOrderItem',
            'isLast'
        ]);
        $invoiceItem->expects($this->any())->method('getOrderItem')->willReturn($orderItem);
        $invoiceItem->expects($this->any())
            ->method('isLast')
            ->willReturn($invoiceItemData['is_last']);
        foreach ($invoiceItemData['data_fields'] as $key => $value) {
            $invoiceItem->setData($key, $value);
        }

        $this->weeeData->expects($this->any())
            ->method('getApplied')
            ->willReturnCallback(function ($item) {
                return $item->getAppliedWeee();
            });

        $this->weeeData->expects($this->any())
            ->method('setApplied')
            ->willReturnCallback(function ($item, $weee) {
                return $item->setAppliedWeee($weee);
            });

        return $invoiceItem;
    }
}
