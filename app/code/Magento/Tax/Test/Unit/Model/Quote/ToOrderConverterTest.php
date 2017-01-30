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

    public function setUp()
    {
        $this->orderExtensionFactoryMock = $this->getMockBuilder(
            '\Magento\Sales\Api\Data\OrderExtensionFactory'
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getAppliedTaxes', 'getItemsAppliedTaxes'])
            ->getMock();
        $this->subjectMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\ToOrder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\Tax\Model\Quote\ToOrderConverter',
            [
                'orderExtensionFactory' => $this->orderExtensionFactoryMock,
            ]
        );
    }

    protected function setupOrderExtensionAttributeMock()
    {
        $orderExtensionAttributeMock = $this->getMockBuilder('\Magento\Sales\Api\Data\OrderExtensionInterface')
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
     * @dataProvider afterConvertDataProvider
     */
    public function testAfterConvert($appliedTaxes, $itemsAppliedTaxes)
    {
        $this->model->beforeConvert($this->subjectMock, $this->quoteAddressMock);

        $this->quoteAddressMock->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemsAppliedTaxes')
            ->willReturn($itemsAppliedTaxes);

        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();

        $orderExtensionAttributeMock = $this->setupOrderExtensionAttributeMock();

        $orderMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($orderExtensionAttributeMock);

        $orderExtensionAttributeMock->expects($this->once())
            ->method('setAppliedTaxes')
            ->with($appliedTaxes);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setConvertingFromQuote')
            ->with(true);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setItemAppliedTaxes')
            ->with($itemsAppliedTaxes);
        $orderMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($orderExtensionAttributeMock);

        $this->assertEquals($orderMock, $this->model->afterConvert($this->subjectMock, $orderMock));
    }

    /**
     * @dataProvider afterConvertDataProvider
     */
    public function testAfterConvertNullExtensionAttribute($appliedTaxes, $itemsAppliedTaxes)
    {
        $this->model->beforeConvert($this->subjectMock, $this->quoteAddressMock);

        $this->quoteAddressMock->expects($this->once())
            ->method('getAppliedTaxes')
            ->willReturn($appliedTaxes);
        $this->quoteAddressMock->expects($this->once())
            ->method('getItemsAppliedTaxes')
            ->willReturn($itemsAppliedTaxes);

        $orderExtensionAttributeMock = $this->setupOrderExtensionAttributeMock();
        
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
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
            ->with($appliedTaxes);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setConvertingFromQuote')
            ->with(true);
        $orderExtensionAttributeMock->expects($this->once())
            ->method('setItemAppliedTaxes')
            ->with($itemsAppliedTaxes);
        $orderMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($orderExtensionAttributeMock);

        $this->assertEquals($orderMock, $this->model->afterConvert($this->subjectMock, $orderMock));
    }

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
                        ]
                    ]
                ],
                'item_applied_taxes' => [
                    'sequence-1' => [
                        [
                            'amount' => 0.06,
                            'item_id' => 146,
                        ],
                    ],
                    'shipping' => [
                        [
                            'amount' => 0.30,
                            'item_type' => 'shipping',
                        ]
                    ],
                ],
            ],
        ];
    }
}
