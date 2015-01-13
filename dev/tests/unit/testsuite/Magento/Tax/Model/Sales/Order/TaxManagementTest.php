<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Order;

use Magento\TestFramework\Helper\ObjectManager;

class TaxManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaxManagement
     */
    private $taxManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxItemResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxItemFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $appliedTaxBuilderMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemBuilderMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderTaxDetailsBuilderMock;

    public function setUp()
    {
        $this->orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->taxItemResourceMock = $this->getMock(
            'Magento\Tax\Model\Resource\Sales\Order\Tax\Item',
            [],
            [],
            '',
            false
        );
        $this->taxItemFactoryMock = $this->getMock(
            'Magento\Tax\Model\Resource\Sales\Order\Tax\ItemFactory',
            ['create'],
            [],
            '',
            false
        );

        $methods = ['setCode', 'setTitle', 'setPercent', 'setAmount', 'setBaseAmount', 'create'];
        $this->appliedTaxBuilderMock
            = $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxDataBuilder', $methods, [], '', false);
        $builderMethods = ['setType', 'setItemId', 'setAssociatedItemId', 'setAppliedTaxes', 'create'];
        $this->itemBuilderMock =
            $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsItemDataBuilder', $builderMethods, [], '', false);
        $detailMethods = ['setItems', 'setAppliedTaxes', 'create'];
        $this->orderTaxDetailsBuilderMock =
            $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsDataBuilder', $detailMethods, [], '', false);

        $objectManager = new ObjectManager($this);
        $this->taxManagement = $objectManager->getObject(
            'Magento\Tax\Model\Sales\Order\TaxManagement',
            [
                'orderFactory' => $this->orderFactoryMock,
                'orderItemTaxFactory' => $this->taxItemFactoryMock,
                'appliedTaxBuilder' => $this->appliedTaxBuilderMock,
                'itemBuilder' => $this->itemBuilderMock,
                'orderTaxDetailsBuilder' => $this->orderTaxDetailsBuilderMock
            ]
        );
    }

    /**
     * @param array $orderItemAppliedTaxes
     * @return void
     * @dataProvider getOrderTaxDetailsDataProvider
     */
    public function testGetOrderTaxDetails($orderItemAppliedTaxes)
    {
        $orderId = 1;
        $appliedTaxDetailsMock = $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface');
        $data = $orderItemAppliedTaxes[0];
        $this->appliedTaxBuilderMock
            ->expects($this->at(0))->method('setCode')->with($data['code']);
        $this->appliedTaxBuilderMock
            ->expects($this->at(1))->method('setTitle')->with($data['title']);
        $this->appliedTaxBuilderMock
            ->expects($this->at(2))->method('setPercent')->with($data['tax_percent']);
        $this->appliedTaxBuilderMock
            ->expects($this->at(3))->method('setAmount')->with($data['real_amount']);
        $this->appliedTaxBuilderMock
            ->expects($this->at(4))->method('setBaseAmount')->with($data['real_base_amount']);
        $this->appliedTaxBuilderMock
            ->expects($this->atLeastOnce())->method('create')->willReturn($appliedTaxDetailsMock);
        $itemMock = $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsItemInterface');
        $this->itemBuilderMock->expects($this->atLeastOnce())->method('create')->willReturn($itemMock);
        $itemMock->expects($this->atLeastOnce())->method('getAppliedTaxes')->willReturn([$appliedTaxDetailsMock]);

        $this->orderFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($this->orderMock));
        $this->orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->will($this->returnSelf());
        $this->taxItemFactoryMock->expects($this->once())->method('create')->willReturn($this->taxItemResourceMock);
        $this->taxItemResourceMock->expects($this->once())
            ->method('getTaxItemsByOrderId')
            ->with($orderId)
            ->will($this->returnValue($orderItemAppliedTaxes));
        $orderTaxDetailsMock = $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsInterface');
        $this->orderTaxDetailsBuilderMock->expects($this->once())->method('create')->willReturn($orderTaxDetailsMock);
        $this->assertEquals($orderTaxDetailsMock, $this->taxManagement->getOrderTaxDetails($orderId));
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOrderTaxDetailsDataProvider()
    {
        return [
            'one_item' => [
                'orderItemAppliedTaxes' => [
                    [
                        'item_id' => 53,
                        'taxable_item_type' => 'product',
                        'associated_item_id' => null,
                        'code' => 'US-CA-*-Rate 1',
                        'title' => 'US-CA-*-Rate 1',
                        'tax_percent' => '8.25',
                        'real_amount' => '6.1889',
                        'real_base_amount' => '12.3779',
                    ],
                ],
            ],
            'wee_item' => [
                'orderItemAppliedTaxes' => [
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 54,
                        'code' => 'SanJose City Tax',
                        'title' => 'SanJose City Tax',
                        'tax_percent' => '6',
                        'real_amount' => '0.9011',
                        'real_base_amount' => '1.7979',
                    ],
                ],
            ],
            'shipping' => [
                'orderItemAppliedTaxes' => [
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'shipping',
                        'associated_item_id' => null,
                        'code' => 'Shipping',
                        'title' => 'Shipping',
                        'tax_percent' => '21',
                        'real_amount' => '2.6',
                        'real_base_amount' => '5.21',
                    ],
                ],
            ],
        ];
    }
}
