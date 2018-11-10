<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Plugin;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OrderSaveTest extends \PHPUnit\Framework\TestCase
{
    const ORDERID = 123;
    const ITEMID = 151;
    const ORDER_ITEM_ID = 116;

    /**
     * @var \Magento\Tax\Model\Sales\Order\TaxFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderTaxFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Tax\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxItemFactoryMock;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Tax\Model\Plugin\OrderSave
     */
    protected $model;

    protected function setUp()
    {
        $this->orderTaxFactoryMock = $this->getMockBuilder(
            \Magento\Tax\Model\Sales\Order\TaxFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxItemFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Tax\ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->subjectMock = $this->getMockForAbstractClass(\Magento\Sales\Api\OrderRepositoryInterface::class);

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\Plugin\OrderSave::class,
            [
                'orderTaxFactory' => $this->orderTaxFactoryMock,
                'taxItemFactory' => $this->taxItemFactoryMock,
            ]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupOrderMock()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getExtensionAttributes',
                    'getAppliedTaxIsSaved',
                    'getItemByQuoteItemId',
                    'setAppliedTaxIsSaved',
                    'getEntityId',
                ]
            )->getMock();

        return $orderMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupExtensionAttributeMock()
    {
        $orderExtensionAttributeMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getAppliedTaxes',
                    'getConvertingFromQuote',
                    'getItemAppliedTaxes',
                ]
            )->getMockForAbstractClass();

        return $orderExtensionAttributeMock;
    }

    /**
     * @param $expectedTaxes
     */
    protected function verifyOrderTaxes($expectedTaxes)
    {
        $index = 0;
        foreach ($expectedTaxes as $orderTaxId => $orderTaxData) {
            $orderTaxMock = $this->getMockBuilder(\Magento\Tax\Model\Sales\Order\Tax::class)
                ->disableOriginalConstructor()
                ->setMethods(
                    [
                        'getTaxId',
                        'setData',
                        'save',
                    ]
                )->getMock();
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
            $this->orderTaxFactoryMock->expects($this->at($index))
                ->method('create')
                ->willReturn($orderTaxMock);
            $index++;
        }
    }

    /**
     * @param $expectedItemTaxes
     */
    public function verifyItemTaxes($expectedItemTaxes)
    {
        $index = 0;
        foreach ($expectedItemTaxes as $itemTax) {
            $itemTaxMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Tax\Item::class)
                ->disableOriginalConstructor()
                ->setMethods(
                    [
                        'setData',
                        'save',
                    ]
                )->getMock();
            $itemTaxMock->expects($this->once())
                ->method('setData')
                ->with($itemTax)
                ->willReturnSelf();
            $itemTaxMock->expects($this->once())
                ->method('save')
                ->willReturnSelf();
            $this->taxItemFactoryMock->expects($this->at($index))
                ->method('create')
                ->willReturn($itemTaxMock);
            $index++;
        }
    }

    /**
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave(
        $appliedTaxes,
        $itemAppliedTaxes,
        $expectedTaxes,
        $expectedItemTaxes
    ) {
        $orderMock = $this->setupOrderMock();

        $extensionAttributeMock = $this->setupExtensionAttributeMock();
        $extensionAttributeMock->expects($this->any())
            ->method('getConvertingFromQuote')
            ->willReturn(true);
        $extensionAttributeMock->expects($this->any())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $extensionAttributeMock->expects($this->any())
            ->method('getItemAppliedTaxes')
            ->willReturn($itemAppliedTaxes);

        $orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $orderItemMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(self::ORDER_ITEM_ID);
        $orderMock->expects($this->once())
            ->method('getAppliedTaxIsSaved')
            ->willReturn(false);
        $orderMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributeMock);
        $orderMock->expects($this->atLeastOnce())
            ->method('getItemByQuoteItemId')
            ->with(self::ITEMID)
            ->willReturn($orderItemMock);
        $orderMock->expects($this->atLeastOnce())
            ->method('getEntityId')
            ->willReturn(self::ORDERID);

        $orderMock->expects($this->once())
            ->method('setAppliedTaxIsSaved')
            ->with(true);

        $this->verifyOrderTaxes($expectedTaxes);
        $this->verifyItemTaxes($expectedItemTaxes);

        $this->assertEquals($orderMock, $this->model->afterSave($this->subjectMock, $orderMock));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function afterSaveDataProvider()
    {
        return [
            //one item with shipping
            //three tax rates: state and national tax rates of 6 and 5 percent with priority 0
            //city tax rate of 3 percent with priority 1
            'item_with_shipping_three_tax' => [
                'applied_taxes' => [
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
                                    'title' => 'IL',
                                ],
                                [
                                    'percent' => 5,
                                    'code' => 'US',
                                    'title' => 'US',
                                ],
                            ]
                        ],
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
                                    'title' => 'CityTax',
                                ],
                            ]
                        ],
                    ],
                ],
                'item_applied_taxes' => [
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
                                            'title' => 'IL',
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US',
                                        ],
                                    ]
                                ],
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
                                            'title' => 'CityTax',
                                        ],
                                    ]
                                ],
                            ],
                        ],
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
                                            'title' => 'IL',
                                        ],
                                        [
                                            'percent' => 5,
                                            'code' => 'US',
                                            'title' => 'US',
                                        ],
                                    ]
                                ],
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
                                            'title' => 'CityTax',
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
                'expected_order_taxes' => [
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
                        'base_real_amount' => 0.36,
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
                        'base_real_amount' => 0.3, //portion for specific rate
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
                        'base_real_amount' => 0.18018018018018, //this number is meaningless since this is single rate
                    ],
                ],
                'expected_item_taxes' => [
                    [
                        //state tax for item
                        'item_id' => self::ORDER_ITEM_ID,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.11,
                        'base_amount' => 0.11,
                        'real_amount' => 0.06,
                        'real_base_amount' => 0.06,
                        'taxable_item_type' => 'product',
                    ],
                    [
                        //state tax for shipping
                        'item_id' => null,
                        'tax_id' => '35',
                        'tax_percent' => 6,
                        'associated_item_id' => null,
                        'amount' => 0.55,
                        'base_amount' => 0.55,
                        'real_amount' => 0.3,
                        'real_base_amount' => 0.3,
                        'taxable_item_type' => 'shipping',
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
                        'taxable_item_type' => 'product',
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
                        'taxable_item_type' => 'shipping',
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
                        'taxable_item_type' => 'product',
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
                        'taxable_item_type' => 'shipping',
                    ],
                ],
            ],
        ];
    }
}
