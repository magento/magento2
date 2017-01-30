<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Framework\DataObject as MagentoObject;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Total\Tax
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
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoice;

    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Model\Order\Creditmemo\Total\Tax $model */
        $this->model = $this->objectManager->getObject('Magento\Sales\Model\Order\Creditmemo\Total\Tax');

        $this->order = $this->getMock(
            '\Magento\Sales\Model\Order',
            [
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->invoice = $this->getMock(
            '\Magento\Sales\Model\Order\Invoice',
            [
                '__wakeup',
            ],
            [],
            '',
            false
        );

        $this->creditmemo = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo',
            [
                'getAllItems',
                'getOrder',
                'roundPrice',
                'isLast',
                '__wakeup',
            ],
            [],
            '',
            false
        );
        $this->creditmemo->expects($this->atLeastOnce())->method('getOrder')->will($this->returnValue($this->order));
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
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item[] $creditmemoItems */
        $creditmemoItems = [];
        foreach ($creditmemoData['items'] as $itemKey => $creditmemoItemData) {
            $creditmemoItems[$itemKey] = $this->getCreditmemoItem($creditmemoItemData);
        }
        $this->creditmemo->expects($this->once())
            ->method('getAllItems')
            ->will($this->returnValue($creditmemoItems));
        $this->creditmemo->expects($this->any())
            ->method('isLast')
            ->will($this->returnValue($creditmemoData['is_last']));
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
                    'invoice' => new MagentoObject(
                            [
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
                    'invoice' => new MagentoObject(
                            [
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
                    'invoice' => new MagentoObject(
                            [
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
                    'grand_total' => 64.95,
                    'base_grand_total' => 64.95,
                    'tax_amount' => 4.95,
                    'base_tax_amount' => 4.95,
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
                    'invoice' => new MagentoObject(
                            [
                                'shipping_tax_amount' => 0,
                                'base_shipping_tax_amount' => 0,
                                'shipping_discount_tax_compensation_amount' => 0,
                                'base_shipping_discount_tax_compensation_amount' => 0,
                            ]
                        ),
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
                    'invoice' => new MagentoObject(
                        [
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

        return $result;
    }

    /**
     * @param $creditmemoItemData array
     * @return \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getCreditmemoItem($creditmemoItemData)
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

        /** @var \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject $creditmemoItem */
        $creditmemoItem = $this->getMock(
            '\Magento\Sales\Model\Order\Creditmemo\Item',
            [
                'getOrderItem',
                'isLast',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $creditmemoItem->expects($this->any())->method('getOrderItem')->will($this->returnValue($orderItem));
        $creditmemoItem->expects($this->any())
            ->method('isLast')
            ->will($this->returnValue($creditmemoItemData['is_last']));
        $creditmemoItem->setData('qty', $creditmemoItemData['qty']);
        return $creditmemoItem;
    }
}
