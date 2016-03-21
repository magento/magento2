<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Model\Total\Creditmemo;

class WeeeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Weee\Model\Total\Creditmemo\Weee
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
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemo;

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
        $this->weeeData = $this->getMockBuilder('\Magento\Weee\Helper\Data')
            ->setMethods(
                [
                    'getRowWeeeTaxInclTax',
                    'getBaseRowWeeeTaxInclTax',
                    'getWeeeAmountInvoiced',
                    'getBaseWeeeAmountInvoiced',
                    'getWeeeAmountRefunded',
                    'getBaseWeeeAmountRefunded',
                    'getWeeeTaxAmountInvoiced',
                    'getBaseWeeeTaxAmountInvoiced',
                    'getWeeeTaxAmountRefunded',
                    'getBaseWeeeTaxAmountRefunded',
                    'getApplied',
                    'setApplied',
                    'includeInSubtotal',
                ]
            )->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Model\Order\Invoice\Total\Tax $model */
        $this->model = $this->objectManager->getObject(
            'Magento\Weee\Model\Total\Creditmemo\Weee',
            [
                'weeeData' => $this->weeeData,
            ]
        );

        $this->order = $this->getMock(
            '\Magento\Sales\Model\Order',
            [
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->creditmemo = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo',
            [
                'getAllItems',
                'getInvoice',
                'roundPrice',
                'getStore',
                '__wakeup',
            ],
            [],
            '',
            false
        );
    }

    /**
     * @param array $creditmemoData
     * @param array $expectedResults
     * @dataProvider collectDataProvider
     */
    public function testCollect($creditmemoData, $expectedResults)
    {
        $roundingDelta = [];

        //Set up weeeData mock
        $this->weeeData->expects($this->once())
            ->method('includeInSubtotal')
            ->will($this->returnValue($creditmemoData['include_in_subtotal']));

        //Set up invoice mock
        /** @var \Magento\Sales\Model\Order\Invoice\Item[] $creditmemoItems */
        $creditmemoItems = [];
        foreach ($creditmemoData['items'] as $itemKey => $creditmemoItemData) {
            $creditmemoItems[$itemKey] = $this->getInvoiceItem($creditmemoItemData);
        }
        $this->creditmemo->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($creditmemoItems));
        foreach ($creditmemoData['data_fields'] as $key => $value) {
            $this->creditmemo->setData($key, $value);
        }
        $this->creditmemo->expects($this->any())
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

        $this->model->collect($this->creditmemo);

        //verify invoice data
        foreach ($expectedResults['creditmemo_data'] as $key => $value) {
            $this->assertEquals(
                $value,
                $this->creditmemo->getData($key),
                'Creditmemo data field '.$key.' is incorrect'
            );
        }
        //verify invoice item data
        foreach ($expectedResults['creditmemo_items'] as $itemKey => $itemData) {
            $creditmemoItem = $creditmemoItems[$itemKey];
            foreach ($itemData as $key => $value) {
                if ($key == 'tax_ratio') {
                    $taxRatio = unserialize($creditmemoItem->getData($key));
                    $expectedTaxRatio = unserialize($itemData[$key]);
                    $this->assertEquals($expectedTaxRatio['weee'], $taxRatio['weee'], "Tax ratio is incorrect");
                } else {
                    $this->assertEquals(
                        $value,
                        $creditmemoItem->getData($key),
                        'Creditmemo item field '.$key.' is incorrect'
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

        // scenario 1: 3 item_1, $100 with $weee, 8.25 tax rate, 3 items invoiced, full creditmemo
        $result['complete_creditmemo'] = [
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 30,
                            'base_weee_amount_invoiced' => 30,
                            'weee_amount_refunded' => 0,
                            'base_weee_amount_refunded' => 0,
                            'weee_tax_amount_invoiced' => 2.47,
                            'base_weee_tax_amount_invoiced' => 2.47,
                            'weee_tax_amount_refunded' => 0,
                            'base_weee_tax_amount_refunded' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'qty_invoiced' => 3,
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
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 300,
                    'base_grand_total' => 300,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 324.75,
                    'base_subtotal_incl_tax' => 324.75,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
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
                        'tax_ratio' => serialize(['weee' => 1.0]),
                        'weee_tax_applied_row_amount' => 30,
                        'base_weee_tax_applied_row_amount' => 30,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 332.47,
                    'base_grand_total' => 332.47,
                    'tax_amount' => 2.47,
                    'base_tax_amount' => 2.47,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 357.22,
                    'base_subtotal_incl_tax' => 357.22,
                ],
            ],
        ];

        // Scenario 2: 3 item_1, $100 with $weee, 8.25 tax rate, 3 items invoiced, 2 item creditmemo
        $result['partial_creditmemo'] = [
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 30,
                            'base_weee_amount_invoiced' => 30,
                            'weee_amount_refunded' => 0,
                            'base_weee_amount_refunded' => 0,
                            'weee_tax_amount_invoiced' => 2.47,
                            'base_weee_tax_amount_invoiced' => 2.47,
                            'weee_tax_amount_refunded' => 0,
                            'base_weee_tax_amount_refunded' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'qty_invoiced' => 3,
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
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 200,
                    'base_grand_total' => 200,
                    'subtotal' => 200,
                    'base_subtotal' => 200,
                    'subtotal_incl_tax' => 216.5,
                    'base_subtotal_incl_tax' => 216.5,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
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
                        'tax_ratio' => serialize(['weee' => 1.65 / 2.47]),
                        'weee_tax_applied_row_amount' => 20,
                        'base_weee_tax_applied_row_amount' => 20,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 221.65,
                    'base_grand_total' => 221.65,
                    'tax_amount' => 1.65,
                    'base_tax_amount' => 1.65,
                    'subtotal' => 200,
                    'base_subtotal' => 200,
                    'subtotal_incl_tax' => 238.15,
                    'base_subtotal_incl_tax' => 238.15,
                ],
            ],
        ];

        // Scenario 3: 3 item_1, $100 with $weee, 8.25 tax rate, 3 items invoiced, 2 item returned
        $result['last_partial_creditmemo'] = [
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 30,
                            'base_weee_amount_invoiced' => 30,
                            'weee_amount_refunded' => 20,
                            'base_weee_amount_refunded' => 20,
                            'weee_tax_amount_invoiced' => 2.47,
                            'base_weee_tax_amount_invoiced' => 2.47,
                            'weee_tax_amount_refunded' => 1.64,
                            'base_weee_tax_amount_refunded' => 1.64,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'qty_invoiced' => 3,
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
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 100,
                    'base_grand_total' => 100,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 108.25,
                    'base_subtotal_incl_tax' => 108.25,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
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
                        'tax_ratio' => serialize(['weee' => 0.83 / 2.47]),
                        'weee_tax_applied_row_amount' => 10,
                        'base_weee_tax_applied_row_amount' => 10,
                    ],
                ],
                'creditmemo_data' => [
                    'grand_total' => 110.83,
                    'base_grand_total' => 110.83,
                    'tax_amount' => 0.83,
                    'base_tax_amount' => 0.83,
                    'subtotal' => 100,
                    'base_subtotal' => 100,
                    'subtotal_incl_tax' => 119.07,
                    'base_subtotal_incl_tax' => 119.07,
                ],
            ],
        ];

        // scenario 4: 3 item_1, $100 with $weee, 8.25 tax rate.  Returning qty 0.
        $result['zero_return'] = [
            'creditmemo_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'weee_tax_applied_row_amount' => 30,
                            'base_weee_tax_applied_row_amnt' => 30,
                            'row_weee_tax_incl_tax' => 32.47,
                            'base_row_weee_tax_incl_tax' => 32.47,
                            'weee_amount_invoiced' => 30,
                            'base_weee_amount_invoiced' => 30,
                            'weee_amount_refunded' => 0,
                            'base_weee_amount_refunded' => 0,
                            'weee_tax_amount_invoiced' => 2.47,
                            'base_weee_tax_amount_invoiced' => 2.47,
                            'weee_tax_amount_refunded' => 0,
                            'base_weee_tax_amount_refunded' => 0,
                            'applied_weee' => [
                                [
                                    'title' => 'recycling_fee',
                                    'base_row_amount' => 30,
                                    'row_amount' => 30,
                                    'base_row_amount_incl_tax' => 32.47,
                                    'row_amount_incl_tax' => 32.47,
                                ],
                            ],
                            'qty_invoiced' => 3,
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
                'include_in_subtotal' => false,
                'data_fields' => [
                    'grand_total' => 300,
                    'base_grand_total' => 300,
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                    'subtotal_incl_tax' => 324.75,
                    'base_subtotal_incl_tax' => 324.75,
                    'tax_amount' => 0,
                    'base_tax_amount' => 0,
                ],
            ],
            'expected_results' => [
                'creditmemo_items' => [
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
                'creditmemo_data' => [
                    'subtotal' => 300,
                    'base_subtotal' => 300,
                ],
            ],
        ];

        return $result;
    }

    /**
     * @param $creditmemoItemData array
     * @return \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInvoiceItem($creditmemoItemData)
    {
        /** @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject $orderItem */
        $orderItem = $this->getMock(
            '\Magento\Sales\Model\Order\Item',
            [
                'isDummy',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        foreach ($creditmemoItemData['order_item'] as $key => $value) {
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
        $this->weeeData->expects($this->once())
            ->method('getWeeeAmountRefunded')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getWeeeAmountRefunded()));
        $this->weeeData->expects($this->once())
            ->method('getBaseWeeeAmountRefunded')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getBaseWeeeAmountRefunded()));
        $this->weeeData->expects($this->once())
            ->method('getWeeeTaxAmountRefunded')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getWeeeTaxAmountRefunded()));
        $this->weeeData->expects($this->once())
            ->method('getBaseWeeeTaxAmountRefunded')
            ->with($orderItem)
            ->will($this->returnValue($orderItem->getBaseWeeeTaxAmountRefunded()));

        /** @var \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject $invoiceItem */
        $invoiceItem = $this->getMock(
            '\Magento\Sales\Model\Order\Invoice\Item',
            [
                'getOrderItem',
                'isLast',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $invoiceItem->expects($this->any())->method('getOrderItem')->will($this->returnValue($orderItem));
        $invoiceItem->expects($this->any())
            ->method('isLast')
            ->will($this->returnValue($creditmemoItemData['is_last']));
        foreach ($creditmemoItemData['data_fields'] as $key => $value) {
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
