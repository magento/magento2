<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Quote;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ToOrderConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderExtensionFactoryMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Tax\Model\Quote\ToOrderConverter
     */
    protected $model;

    protected function setUp()
    {
        $this->orderExtensionFactoryMock = $this->getMockBuilder(
            \Magento\Sales\Api\Data\OrderExtensionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAppliedTaxes', 'getItemsAppliedTaxes'])
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\ToOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Tax\Model\Quote\ToOrderConverter::class,
            [
                'orderExtensionFactory' => $this->orderExtensionFactoryMock,
            ]
        );
    }

    protected function setupOrderExtensionAttributeMock()
    {
        $orderExtensionAttributeMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderExtensionInterface::class)
            ->setMethods(
                [
                    'setAppliedTaxes',
                    'setConvertingFromQuote',
                    'setItemAppliedTaxes'
                ]
            )->getMockForAbstractClass();

        return $orderExtensionAttributeMock;
    }

    /**
     * @param array $appliedTaxes
     * @param array $expectedAppliedTaxes
     * @param array $itemsAppliedTaxes
     * @param array $itemAppliedTaxesExpected
     * @dataProvider afterConvertDataProvider
     */
    public function testAfterConvert(
        $appliedTaxes,
        $expectedAppliedTaxes,
        $itemsAppliedTaxes,
        $itemAppliedTaxesExpected
    ) {
        $this->model->beforeConvert($this->subjectMock, $this->quoteAddressMock);

        $this->quoteAddressMock->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemsAppliedTaxes')
            ->willReturn($itemsAppliedTaxes);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderExtensionAttributeMock = $this->setupOrderExtensionAttributeMock();

        $orderMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($orderExtensionAttributeMock);

        $orderExtensionAttributeMock->expects($this->once())
            ->method('setAppliedTaxes')
            ->with($expectedAppliedTaxes);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setConvertingFromQuote')
            ->with(true);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setItemAppliedTaxes')
            ->with($itemAppliedTaxesExpected);
        $orderMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($orderExtensionAttributeMock);

        $this->assertEquals($orderMock, $this->model->afterConvert($this->subjectMock, $orderMock));
    }

    /**
     * @param array $appliedTaxes
     * @param array $expectedAppliedTaxes
     * @param array $itemsAppliedTaxes
     * @param array $itemAppliedTaxesExpected
     * @dataProvider afterConvertDataProvider
     */
    public function testAfterConvertNullExtensionAttribute(
        $appliedTaxes,
        $expectedAppliedTaxes,
        $itemsAppliedTaxes,
        $itemAppliedTaxesExpected
    ) {
        $this->model->beforeConvert($this->subjectMock, $this->quoteAddressMock);

        $this->quoteAddressMock->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemsAppliedTaxes')
            ->willReturn($itemsAppliedTaxes);

        $orderExtensionAttributeMock = $this->setupOrderExtensionAttributeMock();
        
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);

        $this->orderExtensionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($orderExtensionAttributeMock);

        $orderExtensionAttributeMock->expects($this->once())
            ->method('setAppliedTaxes')
            ->with($expectedAppliedTaxes);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setConvertingFromQuote')
            ->with(true);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setItemAppliedTaxes')
            ->with($itemAppliedTaxesExpected);
        $orderMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($orderExtensionAttributeMock);

        $this->assertEquals($orderMock, $this->model->afterConvert($this->subjectMock, $orderMock));
    }

    /**
     * Data provider for testAfterConvert and testAfterConvertNullExtensionAttribute
     *
     * @return array
     */
    public function afterConvertDataProvider()
    {
        return [
            'afterConvert' => [
                'applied_taxes' => [
                    'IL' => [
                        'amount' => 0.36,
                        'percent' => 6,
                        'rates' => [
                            [
                                'percent' => 6,
                                'code' => 'IL',
                                'title' => 'IL',
                            ]
                        ],
                    ],
                ],
                'expected_applied_taxes' => [
                    'IL' => [
                        'amount' => 0.36,
                        'percent' => 6,
                        'extension_attributes' => [
                            'rates' => [
                                [
                                    'percent' => 6,
                                    'code' => 'IL',
                                    'title' => 'IL',
                                ]
                            ],
                        ],
                    ],
                ],
                'item_applied_taxes' => [
                    'sequence-1' => [
                        [
                            'amount' => 0.06,
                            'item_id' => 146,
                            'item_type' => 'product',
                            'associated_item_id' => null,
                            'rates' => [
                                    [
                                        'percent' => 6,
                                        'code' => 'IL',
                                        'title' => 'IL',
                                    ],
                                ],
                        ],
                    ],
                    'shipping' => [
                        [
                            'amount' => 0.30,
                            'item_id' => 146,
                            'item_type' => 'shipping',
                            'associated_item_id' => null,
                            'rates' => [
                                [
                                    'percent' => 6,
                                    'code' => 'IL',
                                    'title' => 'IL',
                                ],
                            ],
                        ]
                    ],
                ],
                'item_applied_taxes_expected' => [
                    'sequence-1' => [
                            'item_id' => 146,
                            'type' => 'product',
                            'associated_item_id' => null,
                            'applied_taxes' => [
                                [
                                'amount' => 0.06,
                                'item_id' => 146,
                                'item_type' => 'product',
                                'associated_item_id' => null,
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL',
                                        ]
                                    ],
                                ],
                                ]
                            ],
                    ],
                    'shipping' => [
                        'item_id' => 146,
                        'type' => 'shipping',
                        'associated_item_id' => null,
                        'applied_taxes' => [
                            [
                                'amount' => 0.30,
                                'item_id' => 146,
                                'item_type' => 'shipping',
                                'associated_item_id' => null,
                                'extension_attributes' => [
                                    'rates' => [
                                        [
                                            'percent' => 6,
                                            'code' => 'IL',
                                            'title' => 'IL',
                                        ]
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
            ],
        ];
    }
}
