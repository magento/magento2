<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Helper;

use Magento\Framework\DataObject as MagentoObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var MockObject
     */
    protected $orderTaxManagementMock;

    /**
     * @var MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var MockObject
     */
    protected $taxConfigMock;

    /**
     * @var MockObject
     */
    protected $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->orderTaxManagementMock = $this->getMockBuilder(OrderTaxManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->priceCurrencyMock = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->taxConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
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
            Data::class,
            [
                'orderTaxManagement' => $this->orderTaxManagementMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'taxConfig' => $this->taxConfigMock,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetCalculatedTaxesEmptySource(): void
    {
        $source = null;
        $this->assertEquals([], $this->helper->getCalculatedTaxes($source));
    }

    /**
     * @return void
     */
    public function testGetCalculatedTaxesForOrder(): void
    {
        $orderId = 1;
        $itemCode = 'test_code';
        $itemAmount = 2;
        $itemBaseAmount = 3;
        $itemTitle = 'Test title';
        $itemPercent = 0.1;

        $expectedAmount = $itemAmount + 1;
        $expectedBaseAmount = $itemBaseAmount + 1;

        $orderDetailsItem = $this->getMockBuilder(OrderTaxDetailsAppliedTaxInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
            ->willReturnMap($roundValues);

        $appliedTaxes = [$orderDetailsItem];

        $orderDetails = $this->getMockBuilder(OrderTaxDetailsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $orderDetails->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->orderTaxManagementMock->expects($this->once())
            ->method('getOrderTaxDetails')
            ->with($orderId)
            ->willReturn($orderDetails);

        $orderMock = $this->getMockBuilder(Order::class)
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
     * Create OrderTaxDetails mock from array of data.
     *
     * @param $inputArray
     *
     * @return MockObject|OrderTaxDetailsInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function mapOrderTaxItemDetail($inputArray): MockObject
    {
        $orderTaxItemDetailsMock = $this->getMockBuilder(OrderTaxDetailsInterface::class)
            ->getMock();
        $itemMocks = [];
        foreach ($inputArray['items'] as $orderTaxDetailsItemData) {
            $itemId = $orderTaxDetailsItemData['item_id'] ?? null;
            $associatedItemId = $orderTaxDetailsItemData['associated_item_id'] ?? null;
            $itemType = $orderTaxDetailsItemData['type'] ?? null;
            $appliedTaxesData = $orderTaxDetailsItemData['applied_taxes'];
            $appliedTaxesMocks = [];
            foreach ($appliedTaxesData as $appliedTaxData) {
                $appliedTaxesMock = $this->getMockBuilder(
                    OrderTaxDetailsAppliedTaxInterface::class
                )->getMock();
                $appliedTaxesMock->expects($this->any())
                    ->method('getAmount')
                    ->willReturn($appliedTaxData['amount']);
                $appliedTaxesMock->expects($this->any())
                    ->method('getBaseAmount')
                    ->willReturn($appliedTaxData['base_amount']);
                $appliedTaxesMock->expects($this->any())
                    ->method('getCode')
                    ->willReturn($appliedTaxData['code']);
                $appliedTaxesMock->expects($this->any())
                    ->method('getTitle')
                    ->willReturn($appliedTaxData['title']);
                $appliedTaxesMock->expects($this->any())
                    ->method('getPercent')
                    ->willReturn($appliedTaxData['percent']);
                $appliedTaxesMocks[] = $appliedTaxesMock;
            }
            $orderTaxDetailsItemMock = $this->getMockBuilder(OrderTaxDetailsItemInterface::class)
                ->getMock();
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getItemId')
                ->willReturn($itemId);
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getAssociatedItemId')
                ->willReturn($associatedItemId);
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getType')
                ->willReturn($itemType);
            $orderTaxDetailsItemMock->expects($this->any())
                ->method('getAppliedTaxes')
                ->willReturn($appliedTaxesMocks);

            $itemMocks[] = $orderTaxDetailsItemMock;
        }
        $orderTaxItemDetailsMock->expects($this->any())
            ->method('getItems')
            ->willReturn($itemMocks);

        return $orderTaxItemDetailsMock;
    }

    /**
     * @return void
     * @dataProvider getCalculatedTaxesForOrderItemsDataProvider
     */
    public function testGetCalculatedTaxesForOrderItems($orderData, $invoiceData, $expectedResults): void
    {
        $orderId = $orderData['order_id'];
        $orderShippingTaxAmount = $orderData['shipping_tax_amount'] ?? 0;
        $orderTaxDetails = $orderData['order_tax_details'];

        /** @var MockObject|Order $orderMock */
        $orderMock = $this->getMockBuilder(Order::class)
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
            ->willReturn($orderTaxDetailsMock);

        $invoiceShippingTaxAmount = $invoiceData['shipping_tax_amount'] ?? 0;
        $invoiceItems = $invoiceData['invoice_items'];
        /** @var MockObject|Invoice $source */
        $source = $this->getMockBuilder(Invoice::class)
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
            ->willReturnCallback(
                function ($arg) {
                    return round((float) $arg, 2);
                }
            );

        $result = $this->helper->getCalculatedTaxes($source);
        foreach ($result as $index => $appliedTax) {
            $expectedTax = $expectedResults[$index];
            foreach ($appliedTax as $attr => $value) {
                $this->assertEquals($expectedTax[$attr], $value, "The " . $attr . " of tax does not match");
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function getCalculatedTaxesForOrderItemsDataProvider(): array
    {
        $data = [
            //Scenario 1: two items, one item with 0 tax
            'two_items_with_one_zero_tax' => [
                'orderData' => [
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
                                        'percent' => 20.0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'invoiceData' => [
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                            [
                                'order_item' => new MagentoObject(
                                    [
                                        'id' => 1,
                                        'tax_amount' => 5.00
                                    ]
                                ),
                                'tax_amount' => 2.50
                            ]
                        ),
                        'item2' => new MagentoObject(
                            [
                                'order_item' => new MagentoObject(
                                    [
                                        'id' => 2,
                                        'tax_amount' => 0.0
                                    ]
                                ),
                                'tax_amount' => 0.0
                            ]
                        )
                    ]
                ],
                'expectedResults' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 2.5,
                        'base_tax_amount' => 2.5
                    ]
                ]
            ],
            //Scenario 2: one item with associated weee tax
            'item_with_weee_tax_partial_invoice' => [
                'orderData' => [
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
                                        'percent' => 20.0
                                    ]
                                ]
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
                                        'percent' => 20.0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'invoiceData' => [
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                            [
                                'order_item' => new MagentoObject(
                                    [
                                        'id' => 1,
                                        'tax_amount' => 5.00
                                    ]
                                ),
                                'tax_amount' => 5.0,
                                //half of weee tax is invoiced
                                'tax_ratio' => json_encode(['weee' => 0.5])
                            ]
                        )
                    ]
                ],
                'expectedResults' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 6.5,
                        'base_tax_amount' => 6.5
                    ]
                ]
            ],
            //Scenario 3: one item, with both shipping and product taxes
            // note that 'shipping tax' is listed before 'product tax'
            'one_item_with_both_shipping_and_product_taxes' => [
                'orderData' => [
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
                                        'percent' => 10.0
                                    ]
                                ]
                            ],
                            'itemTax1' => [
                                'item_id' => 1,
                                'applied_taxes' => [
                                    [
                                        'amount' => 5.0,
                                        'base_amount' => 5.0,
                                        'code' => 'US-CA',
                                        'title' => 'US-CA-Sales-Tax',
                                        'percent' => 20.0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'invoiceData' => [
                    'shipping_tax_amount' => 2,
                    'invoice_items' => [
                        'item1' => new MagentoObject(
                            [
                                'order_item' => new MagentoObject(
                                    [
                                        'id' => 1,
                                        'tax_amount' => 5.00
                                    ]
                                ),
                                'tax_amount' => 5.00
                            ]
                        )
                    ]
                ],
                // note that 'shipping tax' is now listed after 'product tax'
                'expectedResults' => [
                    [
                        'title' => 'US-CA-Sales-Tax',
                        'percent' => 20.0,
                        'tax_amount' => 5.00,
                        'base_tax_amount' => 5.00
                    ],
                    [
                        'title' => 'US-CA-Sales-Tax-Ship',
                        'percent' => 10.0,
                        'tax_amount' => 2.00,
                        'base_tax_amount' => 2.00
                    ]
                ]
            ]
        ];

        return $data;
    }

    /**
     * @param bool $expected
     * @param bool $displayBothPrices
     * @param bool $priceIncludesTax
     * @param bool $isCrossBorderTradeEnabled
     * @param bool $displayPriceIncludingTax
     *
     * @return void
     * @dataProvider dataProviderIsCatalogPriceDisplayAffectedByTax
     */
    public function testIsCatalogPriceDisplayAffectedByTax(
        $expected,
        $displayBothPrices,
        $priceIncludesTax,
        $isCrossBorderTradeEnabled,
        $displayPriceIncludingTax
    ): void {
        $willReturnArgs = [];

        if ($displayBothPrices == true) {
            $willReturnArgs[] = 3;
        } else {
            $willReturnArgs[] = 2;

            $this->taxConfigMock->expects($this->any())
                ->method('priceIncludesTax')
                ->willReturn($priceIncludesTax);

            $this->taxConfigMock->expects($this->any())
                ->method('crossBorderTradeEnabled')
                ->willReturn($isCrossBorderTradeEnabled);

            if ($displayPriceIncludingTax == true) {
                $willReturnArgs[] = 2;
            } else {
                $willReturnArgs[] = 1;
            }
        }
        $this->taxConfigMock
            ->method('getPriceDisplayType')
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);

        $this->assertSame($expected, $this->helper->isCatalogPriceDisplayAffectedByTax(null));
    }

    /**
     * @return array
     */
    public static function dataProviderIsCatalogPriceDisplayAffectedByTax(): array
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
