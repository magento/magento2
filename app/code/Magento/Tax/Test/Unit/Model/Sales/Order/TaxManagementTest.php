<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item;
use Magento\Sales\Model\ResourceModel\Order\Tax\ItemFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Sales\Order\Details;
use Magento\Tax\Model\Sales\Order\Tax;
use Magento\Tax\Model\Sales\Order\TaxManagement;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxManagementTest extends TestCase
{
    /**
     * @var TaxManagement
     */
    private $taxManagement;

    /**
     * @var MockObject
     */
    private $orderMock;

    /**
     * @var MockObject
     */
    private $taxItemResourceMock;

    /**
     * @var OrderTaxDetailsAppliedTaxInterface
     */
    protected $appliedTaxDataObject;

    /**
     * @var Details
     */
    protected $orderTaxDetailsDataObject;

    protected function setUp(): void
    {
        $this->orderMock = $this->createPartialMock(Order::class, ['load']);

        $orderFactoryMock = $this->createPartialMock(OrderFactory::class, ['create']);
        $orderFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->orderMock);

        $this->taxItemResourceMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTaxItemsByOrderId'])
            ->getMock();

        $taxItemFactoryMock = $this->createPartialMock(ItemFactory::class, ['create']);
        $taxItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->taxItemResourceMock);

        $objectManager = new ObjectManager($this);
        $this->appliedTaxDataObject = $objectManager->getObject(Tax::class);

        $appliedTaxDataObjectFactoryMock = $this->createPartialMock(
            OrderTaxDetailsAppliedTaxInterfaceFactory::class,
            ['create']
        );
        $appliedTaxDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxDataObject);

        $itemDataObject = $objectManager->getObject(\Magento\Sales\Model\Order\Tax\Item::class);

        $itemDataObjectFactoryMock = $this->createPartialMock(OrderTaxDetailsItemInterfaceFactory::class, ['create']);
        $itemDataObjectFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($itemDataObject);

        $this->orderTaxDetailsDataObject = $objectManager->getObject(Details::class);

        $orderTaxDetailsDataObjectFactoryMock = $this->createPartialMock(
            OrderTaxDetailsInterfaceFactory::class,
            ['create']
        );
        $orderTaxDetailsDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->orderTaxDetailsDataObject);

        $this->taxManagement = $objectManager->getObject(
            TaxManagement::class,
            [
                'orderFactory' => $orderFactoryMock,
                'orderItemTaxFactory' => $taxItemFactoryMock,
                'orderTaxDetailsDataObjectFactory' => $orderTaxDetailsDataObjectFactoryMock,
                'itemDataObjectFactory' => $itemDataObjectFactoryMock,
                'appliedTaxDataObjectFactory' => $appliedTaxDataObjectFactoryMock
            ]
        );
    }

    /**
     * @param array $orderItemAppliedTaxes
     * @param array $expected
     * @return void
     * @dataProvider getOrderTaxDetailsDataProvider
     */
    public function testGetOrderTaxDetails($orderItemAppliedTaxes, $expected)
    {
        $orderId = 1;
        $this->orderMock->expects($this->once())
            ->method('load')
            ->with($orderId)->willReturnSelf();
        $this->taxItemResourceMock->expects($this->once())
            ->method('getTaxItemsByOrderId')
            ->with($orderId)
            ->willReturn($orderItemAppliedTaxes);

        $this->assertEquals($this->orderTaxDetailsDataObject, $this->taxManagement->getOrderTaxDetails($orderId));

        $this->assertEquals($expected['code'], $this->appliedTaxDataObject->getCode());
        $this->assertEquals($expected['title'], $this->appliedTaxDataObject->getTitle());
        $this->assertEquals($expected['tax_percent'], $this->appliedTaxDataObject->getPercent());
        $this->assertEquals($expected['real_amount'], $this->appliedTaxDataObject->getAmount());
        $this->assertEquals($expected['real_base_amount'], $this->appliedTaxDataObject->getBaseAmount());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOrderTaxDetailsDataProvider()
    {
        $data = [
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
                'expected' => [
                    'code' => 'US-CA-*-Rate 1',
                    'title' => 'US-CA-*-Rate 1',
                    'tax_percent' => '8.25',
                    'real_amount' => '6.1889',
                    'real_base_amount' => '12.3779',
                ],
            ],

            'weee_item' => [
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
                'expected' => [
                    'code' => 'SanJose City Tax',
                    'title' => 'SanJose City Tax',
                    'tax_percent' => '6',
                    'real_amount' => '0.9011',
                    'real_base_amount' => '1.7979',
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
                'expected' => [
                    'code' => 'Shipping',
                    'title' => 'Shipping',
                    'tax_percent' => '21',
                    'real_amount' => '2.6',
                    'real_base_amount' => '5.21',
                ],
            ],

            'canadian_weee' => [
                'orderItemAppliedTaxes' => [
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 69,
                        'code' => 'GST',
                        'title' => 'GST',
                        'tax_percent' => '5',
                        'real_amount' => '2.10',
                        'real_base_amount' => '4.10',
                    ],
                    [
                        'item_id' => null,
                        'taxable_item_type' => 'weee',
                        'associated_item_id' => 69,
                        'code' => 'GST',
                        'title' => 'GST',
                        'tax_percent' => '5',
                        'real_amount' => '1.15',
                        'real_base_amount' => '3.10',
                    ],
                ],
                'expected' => [
                    'code' => 'GST',
                    'title' => 'GST',
                    'tax_percent' => '5',
                    'real_amount' => '3.25',
                    'real_base_amount' => '7.20',
                ],
            ],
        ];

        return $data;
    }
}
