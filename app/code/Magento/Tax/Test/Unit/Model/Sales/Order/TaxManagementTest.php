<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model\Sales\Order;

use \Magento\Tax\Model\Sales\Order\TaxManagement;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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
    private $appliedTaxDataObjectFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDataObjectFactoryMock;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsItemInterface
     */
    protected $itemDataObject;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderTaxDetailsDataObjectFactoryMock;

    /**
     * @var \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface
     */
    protected $appliedTaxDataObject;

    /**
     * @var \Magento\Tax\Model\Sales\Order\Details
     */
    protected $orderTaxDetailsDataObject;

    public function setUp()
    {
        $this->orderMock = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $this->orderFactoryMock = $this->getMock('Magento\Sales\Model\OrderFactory', ['create'], [], '', false);
        $this->taxItemResourceMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Tax\Item',
            [],
            [],
            '',
            false
        );
        $this->taxItemFactoryMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory',
            ['create'],
            [],
            '',
            false
        );

        $objectManager = new ObjectManager($this);
        $methods = ['create'];
        $this->appliedTaxDataObject = $objectManager->getObject('Magento\Tax\Model\Sales\Order\Tax');
        $this->appliedTaxDataObjectFactoryMock
            = $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterfaceFactory', $methods, [], '', false);
        $this->appliedTaxDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxDataObject);

        $this->itemDataObject = $objectManager->getObject('Magento\Sales\Model\Order\Tax\Item');
        $this->itemDataObjectFactoryMock =
            $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory', $methods, [], '', false);
        $this->itemDataObjectFactoryMock->expects($this->atLeastOnce())
            ->method('create')->willReturn($this->itemDataObject);

        $this->orderTaxDetailsDataObject = $objectManager->getObject('Magento\Tax\Model\Sales\Order\Details');
        $this->orderTaxDetailsDataObjectFactoryMock =
            $this->getMock('Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory', $methods, [], '', false);
        $this->orderTaxDetailsDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderTaxDetailsDataObject);

        $this->taxManagement = $objectManager->getObject(
            'Magento\Tax\Model\Sales\Order\TaxManagement',
            [
                'orderFactory' => $this->orderFactoryMock,
                'orderItemTaxFactory' => $this->taxItemFactoryMock,
                'appliedTaxDataObjectFactory' => $this->appliedTaxDataObjectFactoryMock,
                'itemDataObjectFactory' => $this->itemDataObjectFactoryMock,
                'orderTaxDetailsDataObjectFactory' => $this->orderTaxDetailsDataObjectFactoryMock
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
        $data = $orderItemAppliedTaxes[0];

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
        $this->assertEquals($this->orderTaxDetailsDataObject, $this->taxManagement->getOrderTaxDetails($orderId));
        $this->assertEquals($data['code'], $this->appliedTaxDataObject->getCode());
        $this->assertEquals($data['title'], $this->appliedTaxDataObject->getTitle());
        $this->assertEquals($data['tax_percent'], $this->appliedTaxDataObject->getPercent());
        $this->assertEquals($data['real_amount'], $this->appliedTaxDataObject->getAmount());
        $this->assertEquals($data['real_base_amount'], $this->appliedTaxDataObject->getBaseAmount());
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
