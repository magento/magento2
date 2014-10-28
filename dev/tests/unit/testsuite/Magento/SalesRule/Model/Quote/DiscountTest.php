<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Quote;

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
                    '__wakeup'
                ]
            )
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder('Magento\Framework\Event\Manager')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\SalesRule\Model\Quote\Discount $discount */
        $this->discount = $this->objectManager->getObject(
            'Magento\SalesRule\Model\Quote\Discount',
            [
                'storeManager' => $this->storeManagerMock,
                'validator' => $this->validatorMock,
                'eventManager' => $this->eventManagerMock
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
            ->setMethods(['getQuote', 'getAllNonNominalItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllNonNominalItems')
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
            ->setMethods(['getNoDiscount', 'getParentItemId', '__wakeup'])
            ->getMock();
        $itemWithParentId->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithParentId->expects($this->once())
            ->method('getParentItemId')
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
            ->setMethods(['getQuote', 'getAllNonNominalItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllNonNominalItems')
            ->willReturn([$itemWithParentId]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }

    public function testCollectItemHasChildren()
    {
        $child = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $child->expects($this->any())
            ->method('getParentItem')
            ->willReturnSelf();
        $child->expects($this->any())
            ->method('getPrice')
            ->willReturn(1);
        $child->expects($this->any())
            ->method('getBaseOriginalPrice')
            ->willReturn(1);

        $itemWithChildren = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNoDiscount',
                    'getParentItemId',
                    'getHasChildren',
                    'isChildrenCalculated',
                    'getChildren',
                    '__wakeup'
                ]
            )
            ->getMock();
        $itemWithChildren->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getParentItemId')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getHasChildren')
            ->willReturn(true);
        $itemWithChildren->expects($this->once())
            ->method('isChildrenCalculated')
            ->willReturn(true);
        $itemWithChildren->expects($this->once())
            ->method('getChildren')
            ->willReturn([$child]);

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
            ->setMethods(['getQuote', 'getAllNonNominalItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllNonNominalItems')
            ->willReturn([$itemWithChildren]);
        $addressMock->expects($this->any())
            ->method('getShippingAmount')
            ->willReturn(true);

        $this->assertInstanceOf(
            'Magento\SalesRule\Model\Quote\Discount',
            $this->discount->collect($addressMock)
        );
    }

    public function testCollectItemHasNoChildren()
    {
        $itemWithChildren = $this->getMockBuilder('Magento\Sales\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNoDiscount',
                    'getParentItemId',
                    'getHasChildren',
                    'isChildrenCalculated',
                    'getChildren',
                    '__wakeup'
                ]
            )
            ->getMock();
        $itemWithChildren->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn(false);
        $itemWithChildren->expects($this->once())
            ->method('getParentItemId')
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
            ->setMethods(['getQuote', 'getAllNonNominalItems', 'getShippingAmount', '__wakeup'])
            ->getMock();
        $addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $addressMock->expects($this->any())
            ->method('getAllNonNominalItems')
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
