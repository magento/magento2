<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\ResourceModel\Sales\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Tax\Item;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Tax\Model\ResourceModel\Sales\Order\ConvertQuoteTaxToOrderTax;
use Magento\Tax\Model\Sales\Order\Tax;
use Magento\Tax\Model\Sales\Order\TaxFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConvertQuoteTaxToOrderTaxTest extends TestCase
{
    private const ORDERID = 123;
    private const ITEMID = 151;
    private const ORDER_ITEM_ID = 116;

    /**
     * @var TaxFactory|MockObject
     */
    private $orderTaxFactoryMock;

    /**
     * @var ItemFactory|MockObject
     */
    private $taxItemFactoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var ConvertQuoteTaxToOrderTax
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->orderTaxFactoryMock = $this->getMockBuilder(TaxFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->taxItemFactoryMock = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            ConvertQuoteTaxToOrderTax::class,
            [
                'orderTaxFactory' => $this->orderTaxFactoryMock,
                'taxItemFactory' => $this->taxItemFactoryMock
            ]
        );
    }

    /**
     * @return MockObject
     */
    protected function setupOrderMock(): MockObject
    {
        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getExtensionAttributes',
                    'getItemByQuoteItemId',
                    'getEntityId'
                ]
            )
            ->addMethods(
                [
                    'getAppliedTaxIsSaved',
                    'setAppliedTaxIsSaved'
                ]
            )
            ->getMock();

        return $orderMock;
    }

    /**
     * @return MockObject
     */
    protected function setupExtensionAttributeMock(): MockObject
    {
        $orderExtensionAttributeMock = $this->getMockBuilder(OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getAppliedTaxes',
                    'getConvertingFromQuote',
                    'getItemAppliedTaxes'
                ]
            )
            ->getMockForAbstractClass();

        return $orderExtensionAttributeMock;
    }

    /**
     * @param $expectedTaxes
     *
     * @return void
     */
    protected function verifyOrderTaxes($expectedTaxes): void
    {
        $willReturnArgs = [];

        foreach ($expectedTaxes as $orderTaxId => $orderTaxData) {
            $orderTaxMock = $this->getMockBuilder(Tax::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['setData', 'save'])
                ->addMethods(['getTaxId'])->getMock();
            $orderTaxMock->expects($this->once())
                ->method('setData')
                ->with($orderTaxData)
                ->willReturnSelf();
            $orderTaxMock->expects($this->once())
                ->method('save')
                ->willReturnSelf();
            $orderTaxMock->expects($this->atLeastOnce())
                ->method('getTaxId')
                ->willReturn($orderTaxId);
            $willReturnArgs[] = $orderTaxMock;
        }
        $this->orderTaxFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);
    }

    /**
     * @param $expectedItemTaxes
     *
     * @return void
     */
    public function verifyItemTaxes($expectedItemTaxes): void
    {
        $willReturnArgs = [];

        foreach ($expectedItemTaxes as $itemTax) {
            $itemTaxMock = $this->getMockBuilder(Item::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['setData', 'save'])->getMock();
            $itemTaxMock->expects($this->once())
                ->method('setData')
                ->with($itemTax)
                ->willReturnSelf();
            $itemTaxMock->expects($this->once())
                ->method('save')
                ->willReturnSelf();
            $willReturnArgs[] = $itemTaxMock;
        }
        $this->taxItemFactoryMock
            ->method('create')
            ->willReturnOnConsecutiveCalls(...$willReturnArgs);
    }

    /**
     * Test for execute method
     *
     * @param array $appliedTaxes
     * @param array $itemAppliedTaxes
     * @param array $expectedTaxes
     * @param array $expectedItemTaxes
     * @param int|null $itemId
     *
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        array $appliedTaxes,
        array $itemAppliedTaxes,
        array $expectedTaxes,
        array $expectedItemTaxes,
        ?int $itemId
    ): void {
        $orderMock = $this->setupOrderMock();

        $extensionAttributeMock = $this->setupExtensionAttributeMock();
        $extensionAttributeMock->method('getConvertingFromQuote')
            ->willReturn(true);
        $extensionAttributeMock->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $extensionAttributeMock->method('getItemAppliedTaxes')
            ->willReturn($itemAppliedTaxes);

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $orderItemMock->method('getId')
            ->willReturn($itemId);
        $orderMock->method('getAppliedTaxIsSaved')
            ->willReturn(false);
        $orderMock->method('getExtensionAttributes')
            ->willReturn($extensionAttributeMock);
        $itemByQuoteId = $itemId ? $orderItemMock : $itemId;
        $orderMock->method('getItemByQuoteItemId')
            ->with(self::ITEMID)
            ->willReturn($itemByQuoteId);
        $orderMock->method('getEntityId')
            ->willReturn(self::ORDERID);

        $orderMock->method('setAppliedTaxIsSaved')
            ->with(true);

        $this->verifyOrderTaxes($expectedTaxes);
        $this->verifyItemTaxes($expectedItemTaxes);

        $this->model->execute($orderMock);
    }

    /**
     * Data provider for testExecute().
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function executeDataProvider(): array
    {
        return [
            //one item with shipping
            //three tax rates: state and national tax rates of 6 and 5 percent with priority 0
            //city tax rate of 3 percent with priority 1
            'item_with_shipping_three_tax' => [
                'appliedTaxes' => [
                    [
                        'amount' => 0.66,
                        'base_amount' => 0.66,
                        'percent' => 11,
                        'id' => 'ILUS',
                        'extension_attributes' => [
                            'rates' => [
                                [
                                    'percent' => 6,
                                    'code' => 'IL',
                                    'title' => 'IL'
                                ],
                                [
                                    'percent' => 5,
                                    'code' => 'US',
                                    'title' => 'US'
                                ]
                            ]
                        ]
                    ],
                    [
                        'amount' => 0.2,
                        'base_amount' => 0.2,
                        'percent' => 3.33,
                        'id' => 'CityTax',
                        'extension_attributes' => [
                            'rates' => [
                                [
                                    'percent' => 3,
                                    'code' => 'CityTax',
                                    'title' => 'CityTax'
                                ]
                            ]
                        ]
                    ]
                ],
                'itemAppliedTaxes' => [
                    //item tax, three tax rates
                    [
                        //first two taxes are combined
                        'item_id' => self::ITEMID,
                        'type' => 'product',
                        'associated_item_id' => null,
                        'applied_taxes' => [
                            [
                                'amount' => 0.11,
                                'base_amount' => 0.11,
                                'percent' => 11,
                                'id' => 'ILUS',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL'
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US'
                                        ]
                                    ]
                                ]
                            ],
                            //city tax
                            [
                                'amount' => 0.03,
                                'base_amount' => 0.03,
                                'percent' => 3.33,
                                'id' => 'CityTax',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 3,
                                            'code' => 'CityTax',
                                            'title' => 'CityTax'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    //shipping tax
                    [
                        //first two taxes are combined
                        'item_id' => null,
                        'type' => 'shipping',
                        'associated_item_id' => null,
                        'applied_taxes' => [
                            [
                                'amount' => 0.55,
                                'base_amount' => 0.55,
                                'percent' => 11,
                                'id' => 'ILUS',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL'
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US'
                                        ]
                                    ]
                                ]
                            ],
                            //city tax
                            [
                                'amount' => 0.17,
                                'base_amount' => 0.17,
                                'percent' => 3.33,
                                'id' => 'CityTax',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 3,
                                            'code' => 'CityTax',
                                            'title' => 'CityTax'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedTaxes' => [
                    //state tax
                    '35' => [
                        'order_id' => self::ORDERID,
                        'code' => 'IL',
                        'title' => 'IL',
                        'hidden' => 0,
                        'percent' => 6,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.66,
                        'base_amount' => 0.66,
                        'process' => 0,
                        'base_real_amount' => 0.36000000000000004
                    ],
                    //federal tax
                    '36' => [
                        'order_id' => self::ORDERID,
                        'code' => 'US',
                        'title' => 'US',
                        'hidden' => 0,
                        'percent' => 5,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.66, //combined amount
                        'base_amount' => 0.66,
                        'process' => 0,
                        'base_real_amount' => 0.30000000000000004 //portion for specific rate
                    ],
                    //city tax
                    '37' => [
                        'order_id' => self::ORDERID,
                        'code' => 'CityTax',
                        'title' => 'CityTax',
                        'hidden' => 0,
                        'percent' => 3,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.2, //combined amount
                        'base_amount' => 0.2,
                        'process' => 0,
                        'base_real_amount' => 0.18018018018018017 //this number is meaningless since this is single rate
                    ]
                ],
                'expectedItemTaxes' => [
                    [
                        //state tax for item
                        'item_id' => self::ORDER_ITEM_ID,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.11,
                        'base_amount' => 0.11,
                        'real_amount' => 0.060000000000000005,
                        'real_base_amount' => 0.060000000000000005,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //state tax for shipping
                        'item_id' => null,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.55,
                        'base_amount' => 0.55,
                        'real_amount' => 0.30000000000000004,
                        'real_base_amount' => 0.30000000000000004,
                        'taxable_item_type' => 'shipping'
                    ],
                    [
                        //federal tax for item
                        'item_id' => self::ORDER_ITEM_ID,
                        'tax_id' => '36',
                        'tax_percent' => 5,
                        'associated_item_id' => null,
                        'amount' => 0.11,
                        'base_amount' => 0.11,
                        'real_amount' => 0.05,
                        'real_base_amount' => 0.05,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //federal tax for shipping
                        'item_id' => null,
                        'tax_id' => '36',
                        'tax_percent' => 5,
                        'associated_item_id' => null,
                        'amount' => 0.55,
                        'base_amount' => 0.55,
                        'real_amount' => 0.25,
                        'real_base_amount' => 0.25,
                        'taxable_item_type' => 'shipping'
                    ],
                    [
                        //city tax for item
                        'item_id' => self::ORDER_ITEM_ID,
                        'tax_id' => '37',
                        'tax_percent' => 3.33,
                        'associated_item_id' => null,
                        'amount' => 0.03,
                        'base_amount' => 0.03,
                        'real_amount' => 0.03,
                        'real_base_amount' => 0.03,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //city tax for shipping
                        'item_id' => null,
                        'tax_id' => '37',
                        'tax_percent' => 3.33,
                        'associated_item_id' => null,
                        'amount' => 0.17,
                        'base_amount' => 0.17,
                        'real_amount' => 0.17,
                        'real_base_amount' => 0.17,
                        'taxable_item_type' => 'shipping'
                    ]
                ],
                'itemId' => self::ORDER_ITEM_ID
            ],
            'associated_item_with_empty_order_quote_item' => [
                'appliedTaxes' => [
                    [
                        'amount' => 0.66,
                        'base_amount' => 0.66,
                        'percent' => 11,
                        'id' => 'ILUS',
                        'extension_attributes' => [
                            'rates' => [
                                [
                                    'percent' => 6,
                                    'code' => 'IL',
                                    'title' => 'IL'
                                ],
                                [
                                    'percent' => 5,
                                    'code' => 'US',
                                    'title' => 'US'
                                ]
                            ]
                        ]
                    ],
                    [
                        'amount' => 0.2,
                        'base_amount' => 0.2,
                        'percent' => 3.33,
                        'id' => 'CityTax',
                        'extension_attributes' => [
                            'rates' => [
                                [
                                    'percent' => 3,
                                    'code' => 'CityTax',
                                    'title' => 'CityTax'
                                ]
                            ]
                        ]
                    ]
                ],
                'itemAppliedTaxes' => [
                    //item tax, three tax rates
                    [
                        //first two taxes are combined
                        'item_id' => null,
                        'type' => 'product',
                        'associated_item_id' => self::ITEMID,
                        'applied_taxes' => [
                            [
                                'amount' => 0.11,
                                'base_amount' => 0.11,
                                'percent' => 11,
                                'id' => 'ILUS',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL'
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US'
                                        ]
                                    ]
                                ]
                            ],
                            //city tax
                            [
                                'amount' => 0.03,
                                'base_amount' => 0.03,
                                'percent' => 3.33,
                                'id' => 'CityTax',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 3,
                                            'code' => 'CityTax',
                                            'title' => 'CityTax'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    //shipping tax
                    [
                        //first two taxes are combined
                        'item_id' => null,
                        'type' => 'shipping',
                        'associated_item_id' => null,
                        'applied_taxes' => [
                            [
                                'amount' => 0.55,
                                'base_amount' => 0.55,
                                'percent' => 11,
                                'id' => 'ILUS',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL'
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US'
                                        ]
                                    ]
                                ]
                            ],
                            //city tax
                            [
                                'amount' => 0.17,
                                'base_amount' => 0.17,
                                'percent' => 3.33,
                                'id' => 'CityTax',
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 3,
                                            'code' => 'CityTax',
                                            'title' => 'CityTax'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'expectedTaxes' => [
                    //state tax
                    '35' => [
                        'order_id' => self::ORDERID,
                        'code' => 'IL',
                        'title' => 'IL',
                        'hidden' => 0,
                        'percent' => 6,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.66,
                        'base_amount' => 0.66,
                        'process' => 0,
                        'base_real_amount' => 0.36000000000000004
                    ],
                    //federal tax
                    '36' => [
                        'order_id' => self::ORDERID,
                        'code' => 'US',
                        'title' => 'US',
                        'hidden' => 0,
                        'percent' => 5,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.66, //combined amount
                        'base_amount' => 0.66,
                        'process' => 0,
                        'base_real_amount' => 0.30000000000000004 //portion for specific rate
                    ],
                    //city tax
                    '37' => [
                        'order_id' => self::ORDERID,
                        'code' => 'CityTax',
                        'title' => 'CityTax',
                        'hidden' => 0,
                        'percent' => 3,
                        'priority' => 0,
                        'position' => 0,
                        'amount' => 0.2, //combined amount
                        'base_amount' => 0.2,
                        'process' => 0,
                        'base_real_amount' => 0.18018018018018017 //this number is meaningless since this is single rate
                    ]
                ],
                'expectedItemTaxes' => [
                    [
                        //state tax for item
                        'item_id' => null,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.11,
                        'base_amount' => 0.11,
                        'real_amount' => 0.060000000000000005,
                        'real_base_amount' => 0.060000000000000005,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //state tax for shipping
                        'item_id' => null,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.55,
                        'base_amount' => 0.55,
                        'real_amount' => 0.30000000000000004,
                        'real_base_amount' => 0.30000000000000004,
                        'taxable_item_type' => 'shipping'
                    ],
                    [
                        //federal tax for item
                        'item_id' => null,
                        'tax_id' => '36',
                        'tax_percent' => 5,
                        'associated_item_id' => null,
                        'amount' => 0.11,
                        'base_amount' => 0.11,
                        'real_amount' => 0.05,
                        'real_base_amount' => 0.05,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //federal tax for shipping
                        'item_id' => null,
                        'tax_id' => '36',
                        'tax_percent' => 5,
                        'associated_item_id' => null,
                        'amount' => 0.55,
                        'base_amount' => 0.55,
                        'real_amount' => 0.25,
                        'real_base_amount' => 0.25,
                        'taxable_item_type' => 'shipping'
                    ],
                    [
                        //city tax for item
                        'item_id' => null,
                        'tax_id' => '37',
                        'tax_percent' => 3.33,
                        'associated_item_id' => null,
                        'amount' => 0.03,
                        'base_amount' => 0.03,
                        'real_amount' => 0.03,
                        'real_base_amount' => 0.03,
                        'taxable_item_type' => 'product'
                    ],
                    [
                        //city tax for shipping
                        'item_id' => null,
                        'tax_id' => '37',
                        'tax_percent' => 3.33,
                        'associated_item_id' => null,
                        'amount' => 0.17,
                        'base_amount' => 0.17,
                        'real_amount' => 0.17,
                        'real_base_amount' => 0.17,
                        'taxable_item_type' => 'shipping'
                    ]
                ],
                'itemId' => null
            ]
        ];
    }
}
