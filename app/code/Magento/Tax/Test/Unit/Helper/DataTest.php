<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Tax\Test\Unit\Helper;

use Magento\Framework\DataObject as MagentoObject;

/**
 * Class DataTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $helper;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $orderTaxManagementMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrencyMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $taxConfigMock;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $serializer;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderTaxManagementMock = $this->getMockBuilder(\Magento\Tax\Api\OrderTaxManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxConfigMock = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );
        $this->helper = $objectManager->getObject(
            \Magento\Tax\Helper\Data::class,
            [
                'orderTaxManagement' => $this->orderTaxManagementMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'taxConfig' => $this->taxConfigMock,
                'serializer' => $this->serializer
            ]
        );
    }

    public function testGetCalculatedTaxesEmptySource()
    {
        $source = null;
        $this->assertEquals([], $this->helper->getCalculatedTaxes($source));
    }

    public function testGetCalculatedTaxesForOrder()
    {
        $orderId = 1;
        $itemCode = 'test_code';
        $itemAmount = 2;
        $itemBaseAmount = 3;
        $itemTitle = 'Test title';
        $itemPercent = 0.1;

        $expectedAmount = $itemAmount + 1;
        $expectedBaseAmount = $itemBaseAmount + 1;

        $orderDetailsItem = $this->getMockBuilder(\Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetailsItem->expects($this->once())
            ->method('getCode')
            ->willReturn($itemCode);
        $orderDetailsItem->expects($this->once())
            ->method('getAmount')
            ->willReturn($itemAmount);
        $orderDetailsItem->expects($this->once())
            ->method('getBaseAmount')
            ->willReturn($itemBaseAmount);
        $orderDetailsItem->expects($this->once())
            ->method('getTitle')
            ->willReturn($itemTitle);
        $orderDetailsItem->expects($this->once())
            ->method('getPercent')
            ->willReturn($itemPercent);

        $roundValues = [
            [$itemAmount, $expectedAmount],
            [$itemBaseAmount, $expectedBaseAmount],
        ];
        $this->priceCurrencyMock->expects($this->exactly(2))
            ->method('round')
            ->will($this->returnValueMap($roundValues));

        $appliedTaxes = [$orderDetailsItem];

        $orderDetails = $this->getMockBuilder(\Magento\Tax\Api\Data\OrderTaxDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderDetails->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->orderTaxManagementMock->expects($this->once())
            ->method('getOrderTaxDetails')
            ->with($orderId)
            ->willReturn($orderDetails);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $result = $this->helper->getCalculatedTaxes($orderMock);
        $this->assertCount(1, $result);
        $this->assertEquals($expectedAmount, $result[0]['tax_amount']);
        $this->assertEquals($expectedBaseAmount, $result[0]['base_tax_amount']);
        $this->assertEquals($itemTitle, $result[0]['title']);
        $this->assertEquals($itemPercent, $result[0]['percent']);
    }

    /**
     * Creat OrderTaxDetails mock from array of data
     *
     * @param $inputArray
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Api\Data\OrderTaxDetailsInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function mapOrderTaxItemDetail($inputArray)
    {
        $orderTaxItemDetailsMock = $this->getMockBuilder(\Magento\Tax\Api\Data\OrderTaxDetailsInterface::class)
            ->getMock();
        $itemMocks = [];
        foreach ($inputArray['items'] as $orderTaxDetailsItemData) {
            $itemId = isset($orderTaxDetailsItemData['item_id']) ? $orderTaxDetailsItemData['item_id'] : null;
            $associatedItemId = isset($orderTaxDetailsItemData['associated_item_id'])
                ? $orderTaxDetailsItemData['associated_item_id']
                : null;
            $itemType = isset($orderTaxDetailsItemData['type']) ? $orderTaxDetailsItemData['type'] : null;
            $appliedTaxesData = $orderTaxDetailsItemData['applied_taxes'];
            $appliedTaxesMocks = [];
            foreach ($appliedTaxesData as $appliedTaxData) {
                $appliedTaxesMock = $this->getMockBuilder(
                    \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface::class)
                    ->getMock();
                $appliedTaxesMock->expects($this->any())
                    ->method('getAmount')
                    ->will($this->returnValue($appliedTaxData['amount']));
                $appliedTaxesMock->expects($this->any())
                    ->method('getBaseAmount')
                    ->will($this->returnValue($appliedTaxData['base_amount']));
                $appliedTaxesMock->expects($this->any())
                    ->method('getCode')
                    ->will($this->returnValue($appliedTaxData['code']));
                $appliedTaxesMock->expects($this->any())
                    ->method('getTitle')
                    ->will($this->returnValue($appliedTaxData['title']));
                $appliedTaxesMock->expects($this->any())
                    ->method('getPercent')
                    ->will($this->returnValue($appliedTaxData['percent']));
                $appliedTaxesMocks[] = $appliedTaxesMock;
            }
            $orderTaxDetailsItemMock = $this->getMockBuilder(\Magento\Tax\Api\Data\OrderTaxDetailsItemInterface::class)
                ->getMock();
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getItemId')
                ->will($this->returnValue($itemId));
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getAssociatedItemId')
                ->will($this->returnValue($associatedItemId));
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($itemType));
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getAppliedTaxes')
                ->will($this->returnValue($appliedTaxesMocks));

            $itemMocks[] = $orderTaxDetailsItemMock;
        }
        $orderTaxItemDetailsMock->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue($itemMocks));

        return $orderTaxItemDetailsMock;
    }

    /**
     * @dataProvider getCalculatedTaxesForOrderItemsDataProvider
     */
    public function testGetCalculatedTaxesForOrderItems($orderData, $invoiceData, $expectedResults)
    {
        $orderId = $orderData['order_id'];
        $orderShippingTaxAmount = isset($orderData['shipping_tax_amount']) ? $orderData['shipping_tax_amount'] : 0;
        $orderTaxDetails = $orderData['order_tax_details'];

        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order $orderMock */
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $orderMock->expects($this->once())
            ->method('getShippingTaxAmount')
            ->willReturn($orderShippingTaxAmount);

        $orderTaxDetailsMock = $this->mapOrderTaxItemDetail($orderTaxDetails);
        $this->orderTaxManagementMock->expects($this->any())
            ->method('getOrderTaxDetails')
            ->with($orderId)
            ->will($this->returnValue($orderTaxDetailsMock));

        $invoiceShippingTaxAmount =
            isset($invoiceData['shipping_tax_amount']) ? $invoiceData['shipping_tax_amount'] : 0;
        $invoiceItems = $invoiceData['invoice_items'];
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Order\Invoice $source */
        $source = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $source->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $source->expects($this->once())
            ->method('getShippingTaxAmount')
            ->willReturn($invoiceShippingTaxAmount);
        $source->expects($this->once())
            ->method('getItems')
            ->willReturn($invoiceItems);

        $this->priceCurrencyMock->expects($this->any())
            ->method('round')
            ->will($this->returnCallback(
                    function ($arg) {
                        return round($arg, 2);
                    }
                )
            );

        $result = $this->helper->getCalculatedTaxes($source);
        foreach ($result as $index => $appliedTax) {
            $expectedTax = $expectedResults[$index];
            foreach ($appliedTax as $attr => $value) {
                $this->assertEquals($expectedTax[$attr], $value, "The ".$attr." of tax does not match");
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function getCalculatedTaxesForOrderItemsDataProvider()
    {
        $data = [
            //Scenario 1: two items, one item with 0 tax
            'two_items_with_one_zero_tax' => [
                'order' => [
                    'order_id' => 1,
                    'shipping_tax_amount' => 0,
                    'order_tax_details' => [
                            'items' => [
                                'itemTax1' => [
                                    'item_id' => 1,
                                    'applied_taxes' => [
                                        [
                                            'amount' => 5.0,
                                            'base_amount' => 5.0,
                                            'code' => 'US-CA',
                                            'title' => 'US-CA-Sales-Tax',
                                            'percent' => 20.0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                ],
                'invoice' => [
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                                [
                                    'order_item' => new MagentoObject(
                                            [
                                                'id' => 1,
                                                'tax_amount' => 5.00,
                                            ]
                                        ),
                                    'tax_amount' => 2.50,
                                ]
                            ),
                        'item2' => new MagentoObject(
                                [
                                    'order_item' => new MagentoObject(
                                            [
                                                'id' => 2,
                                                'tax_amount' => 0.0,
                                            ]
                                        ),
                                    'tax_amount' => 0.0,
                                ]
                            ),
                    ],
                ],
                'expected_results' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 2.5,
                        'base_tax_amount' => 2.5,
                    ],
                ],
            ],
            //Scenario 2: one item with associated weee tax
            'item_with_weee_tax_partial_invoice' => [
                'order' => [
                    'order_id' => 1,
                    'shipping_tax_amount' => 0,
                    'order_tax_details' => [
                            'items' => [
                                'itemTax1' => [
                                    'item_id' => 1,
                                    'applied_taxes' => [
                                        [
                                            'amount' => 5.0,
                                            'base_amount' => 5.0,
                                            'code' => 'US-CA',
                                            'title' => 'US-CA-Sales-Tax',
                                            'percent' => 20.0,
                                        ],
                                    ],
                                ],
                                'weeeTax1' => [
                                    'associated_item_id' => 1,
                                    'type' => 'weee',
                                    'applied_taxes' => [
                                        [
                                            'amount' => 3.0,
                                            'base_amount' => 3.0,
                                            'code' => 'US-CA',
                                            'title' => 'US-CA-Sales-Tax',
                                            'percent' => 20.0,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                ],
                'invoice' => [
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                                [
                                    'order_item' => new MagentoObject(
                                            [
                                                'id' => 1,
                                                'tax_amount' => 5.00,
                                            ]
                                        ),
                                    'tax_amount' => 5.0,
                                    //half of weee tax is invoiced
                                    'tax_ratio' => json_encode(['weee' => 0.5]),
                                ]
                            ),
                    ],
                ],
                'expected_results' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 6.5,
                        'base_tax_amount' => 6.5,
                    ],
                ],
            ],
            //Scenario 3: one item, with both shipping and product taxes
            // note that 'shipping tax' is listed before 'product tax'
            'one_item_with_both_shipping_and_product_taxes' => [
                'order' => [
                    'order_id' => 1,
                    'shipping_tax_amount' => 2,
                    'order_tax_details' => [
                        'items' => [
                            'shippingTax1' => [
                                'item_id' => null,
                                'type' => 'shipping',
                                'applied_taxes' => [
                                    [
                                        'amount' => 2.0,
                                        'base_amount' => 2.0,
                                        'code' => 'US-CA-Ship',
                                        'title' => 'US-CA-Sales-Tax-Ship',
                                        'percent' => 10.0,
                                    ],
                                ],
                            ],
                            'itemTax1' => [
                                'item_id' => 1,
                                'applied_taxes' => [
                                    [
                                        'amount' => 5.0,
                                        'base_amount' => 5.0,
                                        'code' => 'US-CA',
                                        'title' => 'US-CA-Sales-Tax',
                                        'percent' => 20.0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'invoice' => [
                    'shipping_tax_amount' => 2,
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                            [
                                'order_item' => new MagentoObject(
                                    [
                                        'id' => 1,
                                        'tax_amount' => 5.00,
                                    ]
                                ),
                                'tax_amount' => 5.00,
                            ]
                        ),
                    ],
                ],
                // note that 'shipping tax' is now listed after 'product tax'
                'expected_results' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 5.00,
                        'base_tax_amount' => 5.00,
                    ],
                    [
                        'title' => 'US-CA-Sales-Tax-Ship',
                        'percent' => 10.0,
                        'tax_amount' => 2.00,
                        'base_tax_amount' => 2.00,
                    ],
                ],
            ],
        ];

        return $data;
    }

    /**
     * @param bool $expected
     * @param bool $displayBothPrices
     * @param bool $priceIncludesTax
     * @param bool $isCrossBorderTradeEnabled
     * @param bool $displayPriceIncludingTax
     * @dataProvider dataProviderIsCatalogPriceDisplayAffectedByTax
     */
    public function testIsCatalogPriceDisplayAffectedByTax($expected, $displayBothPrices, $priceIncludesTax,
        $isCrossBorderTradeEnabled, $displayPriceIncludingTax)
    {
        if ($displayBothPrices == true) {
            $this->taxConfigMock->expects($this->at(0))
                ->method('getPriceDisplayType')
                ->willReturn(3);
        } else {
            $this->taxConfigMock->expects($this->at(0))
                ->method('getPriceDisplayType')
                ->willReturn(2);

            $this->taxConfigMock->expects($this->any())
                ->method('priceIncludesTax')
                ->willReturn($priceIncludesTax);

            $this->taxConfigMock->expects($this->any())
                ->method('crossBorderTradeEnabled')
                ->willReturn($isCrossBorderTradeEnabled);

            if ($displayPriceIncludingTax == true) {
                $this->taxConfigMock->expects($this->at(3))
                    ->method('getPriceDisplayType')
                    ->willReturn(2);
            } else {
                $this->taxConfigMock->expects($this->at(2))
                    ->method('getPriceDisplayType')
                    ->willReturn(1);
            }
        }

        $this->assertSame($expected, $this->helper->isCatalogPriceDisplayAffectedByTax(null));
    }

    /**
     * @return array
     */
    public function dataProviderIsCatalogPriceDisplayAffectedByTax()
    {
        return [
            [true , true, false, false, false],
            [true , false, true, true, false],
            [true , false, true, false, true],
            [false , false, true, true, true],
            [true , false, false, true, true],
            [false , false, false, true, false]
        ];
    }
}
