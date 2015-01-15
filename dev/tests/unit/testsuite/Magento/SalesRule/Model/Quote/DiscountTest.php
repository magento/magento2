<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote;

use Magento\Framework\Object as MagentoObject;

/**
 * Class DiscountTest
 */
class DiscountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Quote\Discount
     */
    protected $discount;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
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

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorMock = $this->getMockBuilder('Magento\SalesRule\Model\Validator')
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
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $priceCurrencyMock = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->getMock();
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->will($this->returnCallback(
                function ($argument) {
                    return round($argument, 2);
                }
            ));

        /** @var \Magento\SalesRule\Model\Quote\Discount $discount */
        $this->discount = $this->objectManager->getObject(
            'Magento\SalesRule\Model\Quote\Discount',
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
        $itemNoDiscount = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getNoDiscount', '__wakeup'])
            ->getMock();
        $itemNoDiscount->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(true);

        $this->validatorMock->expects($this->any())
            ->method('sortItemsByPriority')
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getAllItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$itemNoDiscount]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }

    public function testCollectItemHasParent()
    {
        $itemWithParentId = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getNoDiscount', 'getParentItem', '__wakeup'])
            ->getMock();
        $itemWithParentId->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithParentId->expects($this->once())
            ->method('getParentItem')
            ->willReturn(true);

        $this->validatorMock->expects($this->any())
            ->method('canApplyDiscount')
            ->willReturn(true);

        $this->validatorMock->expects($this->any())
            ->method('sortItemsByPriority')
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getAllItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$itemWithParentId]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }

    /**
     * @dataProvider collectItemHasChildrenDataProvider
     */
    public function testCollectItemHasChildren($childItemData, $parentData, $expectedChildData)
    {
        $childItems = [];
        foreach ($childItemData as $itemId => $itemData) {
            $childItems[$itemId] = new MagentoObject($itemData);
        }

        $itemWithChildren = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
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
        $itemWithChildren->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getParentItem')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getHasChildren')
            ->willReturn(true);
        $itemWithChildren->expects($this->once())
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemWithChildren->expects($this->any())
            ->method('getChildren')
            ->willReturn($childItems);
        foreach ($parentData as $key => $value) {
            $itemWithChildren->setData($key, $value);
        }

        $this->validatorMock->expects($this->any())
            ->method('canApplyDiscount')
            ->willReturn(true);

        $this->validatorMock->expects($this->any())
            ->method('sortItemsByPriority')
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->any())
            ->method('canApplyRules')
            ->willReturn(true);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getAllItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$itemWithChildren]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
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
        $itemWithChildren = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
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
        $itemWithChildren->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getParentItem')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getHasChildren')
            ->willReturn(false);

        $this->validatorMock->expects($this->any())
            ->method('canApplyDiscount')
            ->willReturn(true);

        $this->validatorMock->expects($this->any())
            ->method('sortItemsByPriority')
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', 'getAllItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn([$itemWithChildren]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }

    public function testFetch()
    {
        $addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['getDiscountAmount', 'getDiscountDescription', 'addTotal', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn(10);
        $addressMock->expects($this->once())
            ->method('getDiscountDescription')
            ->willReturn('test description');

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->fetch($addressMock)
        );
    }
}
