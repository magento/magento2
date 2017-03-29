<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Total;

class TaxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\Total\Tax
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

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Model\Order\Invoice\Total\Tax $model */
        $this->model = $this->objectManager->getObject(\Magento\Sales\Model\Order\Invoice\Total\Tax::class);

        $this->order = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [
                'getInvoiceCollection',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->invoice = $this->getMock(
            \Magento\Sales\Model\Order\Invoice::class,
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
        $this->invoice->expects($this->atLeastOnce())->method('getOrder')->will($this->returnValue($this->order));
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

        //Set up order mock
        foreach ($orderData['data_fields'] as $key => $value) {
            $this->order->setData($key, $value);
        }
        /** @var \Magento\Sales\Model\Order\Invoice[] $previousInvoices */
        $previousInvoices = [];
        foreach ($orderData['previous_invoices'] as $previousInvoiceData) {
            $previousInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
                ->disableOriginalConstructor()
                ->setMethods(['isCanceled', '__wakeup'])
                ->getMock();
            $previousInvoice->setData('shipping_amount', $previousInvoiceData['shipping_amount']);
            $previousInvoices[] = $previousInvoice;
        }

        $this->order->expects($this->once())
            ->method('getInvoiceCollection')
            ->will($this->returnValue($previousInvoices));

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
            $this->assertEquals($value, $this->invoice->getData($key));
        }
        //verify invoice item data
        foreach ($expectedResults['invoice_items'] as $itemKey => $itemData) {
            $invoiceItem = $invoiceItems[$itemKey];
            foreach ($itemData as $key => $value) {
                $this->assertEquals($value, $invoiceItem->getData($key));
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
        // 3 item_1, 3 item_2, $99 each, 8.19 tax rate
        $result['partial_invoice'] = [
            'order_data' => [
                'previous_invoices' => [
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 2.45,
                    'base_shipping_tax_amount' => 2.45,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 53.56,
                    'base_tax_amount' => 53.56,
                ],
            ],
            'invoice_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.32,
                            'tax_invoiced' => 0,
                            'base_tax_amount' => 24.32,
                            'base_tax_invoiced' => 0,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.33,
                            'tax_invoiced' => 0,
                            'base_tax_amount' => 24.33,
                            'base_tax_invoiced' => 0,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 0,
                        ],
                        'is_last' => false,
                        'qty' => 2,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 327,
                    'base_grand_total' => 327,
                ],
            ],
            'expected_results' => [
                'invoice_items' => [
                    'item_1' => [
                        'tax_amount' => 8.11,
                        'base_tax_amount' => 8.11,
                    ],
                    'item_2' => [
                        'tax_amount' => 16.22,
                        'base_tax_amount' => 16.22,
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 353.78,
                    'base_grand_total' => 353.78,
                    'tax_amount' => 26.78,
                    'base_tax_amount' => 26.78,
                ],
            ],
        ];

        // 3 item_1, 3 item_2, $99 each, 8.19 tax rate
        // item_1 has 1 already invoiced, item_2 has 2 already invoiced
        $result['partial_invoice_second_invoice'] = [
            'order_data' => [
                'previous_invoices' => [
                    [
                        'shipping_amount' => 30,
                        'is_canceled' => false,
                    ],
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 2.45,
                    'base_shipping_tax_amount' => 2.45,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 53.56,
                    'base_tax_amount' => 53.56,
                ],
            ],
            'invoice_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.32,
                            'tax_invoiced' => 8.11,
                            'base_tax_amount' => 24.32,
                            'base_tax_invoiced' => 8.11,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 1,
                        ],
                        'is_last' => false,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.33,
                            'tax_invoiced' => 16.22,
                            'base_tax_amount' => 24.33,
                            'base_tax_invoiced' => 16.22,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 2,
                        ],
                        'is_last' => false,
                        'qty' => 0,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 99,
                    'base_grand_total' => 99,
                ],
            ],
            'expected_results' => [
                'invoice_items' => [
                    'item_1' => [
                        'tax_amount' => 8.11,
                        'base_tax_amount' => 8.11,
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 107.11,
                    'base_grand_total' => 107.11,
                    'tax_amount' => 8.11,
                    'base_tax_amount' => 8.11,
                ],
            ],
        ];
        // 3 item_1, 3 item_2, $99 each, 8.19 tax rate
        // item_1 has 1 already invoiced, item_2 has 2 already invoiced
        $result['partial_invoice_last_invoice'] = [
            'order_data' => [
                'previous_invoices' => [
                    [
                        'shipping_amount' => 30,
                        'is_canceled' => false,
                    ],
                ],
                'data_fields' => [
                    'shipping_tax_amount' => 2.45,
                    'base_shipping_tax_amount' => 2.45,
                    'shipping_discount_tax_compensation_amount' => 0,
                    'base_shipping_discount_tax_compensation_amount' => 0,
                    'tax_amount' => 53.56,
                    'base_tax_amount' => 53.56,
                ],
            ],
            'invoice_data' => [
                'items' => [
                    'item_1' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.32,
                            'tax_invoiced' => 16.22,
                            'base_tax_amount' => 24.32,
                            'base_tax_invoiced' => 16.22,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 2,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                    'item_2' => [
                        'order_item' => [
                            'qty_ordered' => 3,
                            'tax_amount' => 24.33,
                            'tax_invoiced' => 16.22,
                            'base_tax_amount' => 24.33,
                            'base_tax_invoiced' => 16.22,
                            'discount_tax_compensation_amount' => 0,
                            'base_discount_tax_compensation_amount' => 0,
                            'qty_invoiced' => 2,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                ],
                'is_last' => false,
                'data_fields' => [
                    'grand_total' => 198,
                    'base_grand_total' => 198,
                ],
            ],
            'expected_results' => [
                'invoice_items' => [
                    'item_1' => [
                        'tax_amount' => 8.10,
                        'base_tax_amount' => 8.10,
                    ],
                    'item_2' => [
                        'tax_amount' => 8.11,
                        'base_tax_amount' => 8.11,
                    ],
                ],
                'invoice_data' => [
                    'grand_total' => 214.21,
                    'base_grand_total' => 214.21,
                    'tax_amount' => 16.21,
                    'base_tax_amount' => 16.21,
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
        $orderItem = $this->getMock(
            \Magento\Sales\Model\Order\Item::class,
            [
                'isDummy',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        foreach ($invoiceItemData['order_item'] as $key => $value) {
            $orderItem->setData($key, $value);
        }

        /** @var \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject $invoiceItem */
        $invoiceItem = $this->getMock(
            \Magento\Sales\Model\Order\Invoice\Item::class,
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
            ->will($this->returnValue($invoiceItemData['is_last']));
        $invoiceItem->setData('qty', $invoiceItemData['qty']);
        return $invoiceItem;
    }
}
