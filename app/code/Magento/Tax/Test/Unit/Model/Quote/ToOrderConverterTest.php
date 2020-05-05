<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Model\Order;
use Magento\Tax\Model\Quote\ToOrderConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ToOrderConverterTest extends TestCase
{
    /**
     * @var OrderExtensionFactory|MockObject
     */
    protected $orderExtensionFactoryMock;

    /**
     * @var Address|MockObject
     */
    protected $quoteAddressMock;

    /**
     * @var ToOrder|MockObject
     */
    protected $subjectMock;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var ToOrderConverter
     */
    protected $model;

    protected function setUp(): void
    {
        $this->orderExtensionFactoryMock = $this->getMockBuilder(
            OrderExtensionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAppliedTaxes', 'getItemsAppliedTaxes'])
            ->getMock();
        $this->subjectMock = $this->getMockBuilder(ToOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            ToOrderConverter::class,
            [
                'orderExtensionFactory' => $this->orderExtensionFactoryMock,
            ]
        );
    }

    /**
     * @return MockObject
     */
    protected function setupOrderExtensionAttributeMock()
    {
        $orderExtensionAttributeMock = $this->getMockBuilder(OrderExtensionInterface::class)
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

        $orderMock = $this->getMockBuilder(Order::class)
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

        $orderMock = $this->getMockBuilder(Order::class)
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
