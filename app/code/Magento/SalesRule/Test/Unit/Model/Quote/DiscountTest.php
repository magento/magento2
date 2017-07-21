<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Framework\DataObject as MagentoObject;

/**
 * Class DiscountTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Quote\Discount
     */
    protected $discount;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAssignmentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->validatorMock = $this->getMockBuilder(\Magento\SalesRule\Model\Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'canApplyRules',
                    'reset',
                    'init',
                    'initTotals',
                    'sortItemsByPriority',
                    'setSkipActionsValidation',
                    'process',
                    'processShippingAmount',
                    'canApplyDiscount',
                    '__wakeup',
                ]
            )
            ->getMock();
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->will($this->returnCallback(
                function ($argument) {
                    return round($argument, 2);
                }
            ));

        $this->addressMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getQuote', 'getAllItems', 'getShippingAmount', '__wakeup', 'getCustomAttributesCodes'],
            [],
            '',
            false
        );
        $this->addressMock->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);

        $shipping = $this->getMock(\Magento\Quote\Api\Data\ShippingInterface::class);
        $shipping->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $this->shippingAssignmentMock = $this->getMock(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class);
        $this->shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shipping);

        /** @var \Magento\SalesRule\Model\Quote\Discount $discount */
        $this->discount = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Quote\Discount::class,
            [
                'storeManager' => $this->storeManagerMock,
                'validator' => $this->validatorMock,
                'eventManager' => $this->eventManagerMock,
                'priceCurrency' => $priceCurrencyMock,
            ]
        );
    }

    public function testCollectItemNoDiscount()
    {
        $itemNoDiscount = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getNoDiscount', '__wakeup'],
            [],
            '',
            false
        );
        $itemNoDiscount->expects($this->once())->method('getNoDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemNoDiscount], $this->addressMock)
            ->willReturnArgument(0);
        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getStore', '__wakeup'], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemNoDiscount]);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);

        $totalMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);

        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Quote\Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    public function testCollectItemHasParent()
    {
        $itemWithParentId = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getNoDiscount', 'getParentItem', '__wakeup'],
            [],
            '',
            false
        );
        $itemWithParentId->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithParentId->expects($this->once())->method('getParentItem')->willReturn(true);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->any())->method('sortItemsByPriority')
            ->with([$itemWithParentId], $this->addressMock)
            ->willReturnArgument(0);

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, ['getStore', '__wakeup'], [], '', false);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithParentId]);
        $totalMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);

        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Quote\Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    /**
     * @dataProvider collectItemHasChildrenDataProvider
     */
    public function testCollectItemHasChildren($childItemData, $parentData, $expectedChildData)
    {
        $childItems = [];
        foreach ($childItemData as $itemId => $itemData) {
            $item = $this->objectManager->getObject(\Magento\Quote\Model\Quote\Item::class)->setData($itemData);
            $childItems[$itemId] = $item;
        }

        $itemWithChildren = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNoDiscount',
                    'getParentItem',
                    'getHasChildren',
                    'isChildrenCalculated',
                    'getChildren',
                    '__wakeup',
                ]
            )
            ->getMock();
        $itemWithChildren->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getParentItem')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getHasChildren')->willReturn(true);
        $itemWithChildren->expects($this->once())->method('isChildrenCalculated')->willReturn(true);
        $itemWithChildren->expects($this->any())->method('getChildren')->willReturn($childItems);
        foreach ($parentData as $key => $value) {
            $itemWithChildren->setData($key, $value);
        }

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemWithChildren], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->any())->method('canApplyRules')->willReturn(true);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);

        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithChildren]);
        $totalMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);

        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Quote\Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );

        foreach ($expectedChildData as $itemId => $expectedItemData) {
            $childItem = $childItems[$itemId];
            foreach ($expectedItemData as $key => $value) {
                $this->assertEquals($value, $childItem->getData($key), 'Incorrect value for ' . $key);
            }
        }
    }

    public function collectItemHasChildrenDataProvider()
    {
        $data = [
            // 3 items, each $100, testing that discount are distributed to item correctly
            'three_items' => [
                'child_item_data' => [
                    'item1' => [
                        'base_row_total' => 100,
                    ],
                    'item2' => [
                        'base_row_total' => 100,
                    ],
                    'item3' => [
                        'base_row_total' => 100,
                    ],
                ],
                'parent_item_data' => [
                    'discount_amount' => 20,
                    'base_discount_amount' => 10,
                    'original_discount_amount' => 40,
                    'base_original_discount_amount' => 20,
                    'base_row_total' => 300,
                ],
                'expected_child_item_data' => [
                    'item1' => [
                        'discount_amount' => 6.67,
                        'base_discount_amount' => 3.33,
                        'original_discount_amount' => 13.33,
                        'base_original_discount_amount' => 6.67,
                    ],
                    'item2' => [
                        'discount_amount' => 6.66,
                        'base_discount_amount' => 3.34,
                        'original_discount_amount' => 13.34,
                        'base_original_discount_amount' => 6.66,
                    ],
                    'item3' => [
                        'discount_amount' => 6.67,
                        'base_discount_amount' => 3.33,
                        'original_discount_amount' => 13.33,
                        'base_original_discount_amount' => 6.67,
                    ],
                ],
            ],
        ];
        return $data;
    }

    public function testCollectItemHasNoChildren()
    {
        $itemWithChildren = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNoDiscount',
                    'getParentItem',
                    'getHasChildren',
                    'isChildrenCalculated',
                    'getChildren',
                    '__wakeup',
                ]
            )
            ->getMock();
        $itemWithChildren->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getParentItem')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getHasChildren')->willReturn(false);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemWithChildren], $this->addressMock)
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithChildren]);

        $totalMock = $this->getMock(\Magento\Quote\Model\Quote\Address\Total::class, [], [], '', false);
        $this->assertInstanceOf(
            \Magento\SalesRule\Model\Quote\Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    public function testFetch()
    {
        $discountAmount = 100;
        $discountDescription = 100;
        $expectedResult = [
            'code' => 'discount',
            'value' => 100,
            'title' => __('Discount (%1)', $discountDescription)
        ];

        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $totalMock = $this->getMock(
            \Magento\Quote\Model\Quote\Address\Total::class,
            ['getDiscountAmount', 'getDiscountDescription'],
            [],
            '',
            false
        );

        $totalMock->expects($this->once())->method('getDiscountAmount')->willReturn($discountAmount);
        $totalMock->expects($this->once())->method('getDiscountDescription')->willReturn($discountDescription);
        $this->assertEquals($expectedResult, $this->discount->fetch($quoteMock, $totalMock));
    }
}
