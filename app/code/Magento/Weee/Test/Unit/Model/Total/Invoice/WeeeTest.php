<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\Total\Invoice;

use Magento\Framework\Serialize\Serializer\Json;

class WeeeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Weee\Model\Total\Invoice\Weee
     */
    protected $model;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoice;

    /**
     * @var \Magento\Weee\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeData;

    protected function setUp()
    {
        $this->weeeData = $this->getMockBuilder(\Magento\Weee\Helper\Data::class)
            ->setMethods(
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

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $serializer = $this->objectManager->getObject(Json::class);
        /** @var \Magento\Sales\Model\Order\Invoice\Total\Tax $model */
        $this->model = $this->objectManager->getObject(
            \Magento\Weee\Model\Total\Invoice\Weee::class,
            [
                'weeeData' => $this->weeeData,
                'serializer' => $serializer
            ]
        );

        $this->order = $this->createPartialMock(\Magento\Sales\Model\Order::class, [
                '__wakeup'
            ]);

        $this->invoice = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice::class, [
                'getAllItems',
                'getOrder',
                'roundPrice',
                'isLast',
                'getStore',
                '__wakeup',
            ]);
        $this->invoice->expects($this->atLeastOnce())->method('getOrder')->will($this->returnValue($this->order));
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
        $this->weeeData->expects($this->once())
            ->method('includeInSubtotal')
            ->will($this->returnValue($invoiceData['include_in_subtotal']));

        //Set up invoice mock
        /** @var \Magento\Sales\Model\Order\Invoice\Item[] $invoiceItems */
        $invoiceItems = [];
        foreach ($invoiceData['items'] as $itemKey => $invoiceItemData) {
            $invoiceItems[$itemKey] = $this->getInvoiceItem($invoiceItemData);
        }
        $this->invoice->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($invoiceItems));
        $this->invoice->expects($this->once())
            ->method('isLast')
            ->will($this->returnValue($invoiceData['is_last']));
        foreach ($invoiceData['data_fields'] as $key => $value) {
            $this->invoice->setData($key, $value);
        }
        $this->invoice->expects($this->any())
            ->method('roundPrice')
            ->will($this->returnCallback(
                function ($price, $type) use (&$roundingDelta) {
                    if (!isset($roundingDelta[$type])) {
                        $roundingDelta[$type] = 0;
                    }
                    $roundedPrice = round($price + $roundingDelta[$type], 2);
                    $roundingDelta[$type] = $price - $roundedPrice;

                    return $roundedPrice;
                }
            ));

        $this->model->collect($this->invoice);

        //verify invoice data
        foreach ($expectedResults['invoice_data'] as $key => $value) {
            $this->assertEquals($value, $this->invoice->getData($key), 'Invoice data field '.$key.' is incorrect');
        }
        //verify invoice item data
        foreach ($expectedResults['invoice_items'] as $itemKey => $itemData) {
            $invoiceItem = $invoiceItems[$itemKey];
            foreach ($itemData as $key => $value) {
                if ($key == 'tax_ratio') {
                    $taxRatio = json_decode($invoiceItem->getData($key), true);
                    $this->assertEquals($value['weee'], $taxRatio['weee'], "Tax ratio is incorrect");
                } else {
                    $this->assertEquals(
                        $value,
                        $invoiceItem->getData($key),
                        'Invoice item field '.$key.' is incorrect'
                    );
                }
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

        // 3 item_1, $100 with $weee, 8.25 tax rate, full invoice
        $result['complete_invoice'] = [
            'order_data' => [
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
            'invoice_data' => [
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
            'expected_results' => [
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
                    'subtotal_incl_tax' => 344.85,
                    'base_subtotal_incl_tax' => 344.85,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate, partial invoice, invoice qty=2
        $result['partial_invoice'] = [
            'order_data' => [
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
            'invoice_data' => [
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
            'expected_results' => [
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
            'order_data' => [
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
            'invoice_data' => [
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
            'expected_results' => [
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
            'order_data' => [
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
            'invoice_data' => [
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
            'expected_results' => [
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
                    'subtotal_incl_tax' => 114.95,
                    'base_subtotal_incl_tax' => 114.95,
                ],
            ],
        ];

        // 3 item_1, $100 with $weee, 8.25 tax rate. Invoicing qty 0.
        $result['zero_invoice'] = [
            'order_data' => [
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
            'invoice_data' => [
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
            'expected_results' => [
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
     * @return \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInvoiceItem($invoiceItemData)
    {
        /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject $orderItem */
        $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, [
                'isDummy',
                '__wakeup'
            ]);
        foreach ($invoiceItemData['order_item'] as $key => $value) {
            $orderItem->setData($key, $value);
        }

        $this->weeeData->expects($this->once())
            ->method('getRowWeeeTaxInclTax')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getRowWeeeTaxInclTax()));
        $this->weeeData->expects($this->once())
            ->method('getBaseRowWeeeTaxInclTax')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getBaseRowWeeeTaxInclTax()));
        if ($invoiceItemData['is_last']) {
            $this->weeeData->expects($this->once())
                ->method('getWeeeAmountInvoiced')
                ->with($orderItem)
                ->will($this->returnValue($orderItem->getWeeeAmountInvoiced()));
            $this->weeeData->expects($this->once())
                ->method('getBaseWeeeAmountInvoiced')
                ->with($orderItem)
                ->will($this->returnValue($orderItem->getBaseWeeeAmountInvoiced()));
            $this->weeeData->expects($this->once())
                ->method('getWeeeTaxAmountInvoiced')
                ->with($orderItem)
                ->will($this->returnValue($orderItem->getWeeeTaxAmountInvoiced()));
            $this->weeeData->expects($this->once())
                ->method('getBaseWeeeTaxAmountInvoiced')
                ->with($orderItem)
                ->will($this->returnValue($orderItem->getBaseWeeeTaxAmountInvoiced()));
        }
        /** @var \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject $invoiceItem */
        $invoiceItem = $this->createPartialMock(\Magento\Sales\Model\Order\Invoice\Item::class, [
                'getOrderItem',
                'isLast',
                '__wakeup'
            ]);
        $invoiceItem->expects($this->any())->method('getOrderItem')->will($this->returnValue($orderItem));
        $invoiceItem->expects($this->any())
            ->method('isLast')
            ->will($this->returnValue($invoiceItemData['is_last']));
        foreach ($invoiceItemData['data_fields'] as $key => $value) {
            $invoiceItem->setData($key, $value);
        }

        $this->weeeData->expects($this->any())
            ->method('getApplied')
            ->will($this->returnCallback(
                function ($item) {
                    return $item->getAppliedWeee();
                }
            ));

        $this->weeeData->expects($this->any())
            ->method('setApplied')
            ->will($this->returnCallback(
                function ($item, $weee) {
                    return $item->setAppliedWeee($weee);
                }
            ));

        return $invoiceItem;
    }
}
