<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Quote\Discount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test discount totals calculation model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends TestCase
{
    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $validatorMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $shippingAssignmentMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var DataFactory|MockObject
     */
    private $discountFactory;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->validatorMock = $this->getMockBuilder(Validator::class)
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
        $this->eventManagerMock = $this->createMock(Manager::class);
        $priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->will(
                $this->returnCallback(
                    function ($argument) {
                        return round($argument, 2);
                    }
                )
            );

        $this->addressMock = $this->createPartialMock(
            Address::class,
            [
                'getQuote',
                'getAllItems',
                'getShippingAmount',
                '__wakeup',
                'getCustomAttributesCodes',
                'getExtensionAttributes'
            ]
        );
        $addressExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->setMethods(['setDiscounts', 'getDiscounts'])->getMock();
        $addressExtension->method('getDiscounts')->willReturn([]);
        $addressExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $this->addressMock->expects(
            $this->any()
        )->method('getExtensionAttributes')->will($this->returnValue($addressExtension));
        $this->addressMock->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $this->shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
        $this->shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shipping);
        $this->discountFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );

        /** @var Discount $discount */
        $this->discount = $this->objectManager->getObject(
            Discount::class,
            [
                'storeManager' => $this->storeManagerMock,
                'validator' => $this->validatorMock,
                'eventManager' => $this->eventManagerMock,
                'priceCurrency' => $priceCurrencyMock,
            ]
        );
        $discountData = $this->getMockBuilder(Data::class)
            ->setConstructorArgs(
                [
                    'amount' => 0,
                    'baseAmount' => 0,
                    'originalAmount' => 0,
                    'baseOriginalAmount' => 0
                ]
            )
            ->getMock();
        $this->discountFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->will($this->returnValue($discountData));
    }

    public function testCollectItemNoDiscount()
    {
        $itemNoDiscount = $this->createPartialMock(
            Item::class,
            ['getNoDiscount', '__wakeup', 'getExtensionAttributes']
        );
        $itemExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->setMethods(['setDiscounts', 'getDiscounts'])->getMock();
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $itemNoDiscount->expects(
            $this->any()
        )->method('getExtensionAttributes')->will($this->returnValue($itemExtension));
        $itemNoDiscount->expects($this->once())->method('getNoDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemNoDiscount], $this->addressMock)
            ->willReturnArgument(0);
        $storeMock = $this->createPartialMock(Store::class, ['getStore', '__wakeup']);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $quoteMock = $this->createMock(Quote::class);
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemNoDiscount]);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);

        $totalMock = $this->createMock(Total::class);

        $this->assertInstanceOf(
            Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    public function testCollectItemHasParent()
    {
        $itemWithParentId = $this->createPartialMock(
            Item::class,
            ['getNoDiscount', 'getParentItem', '__wakeup']
        );
        $itemWithParentId->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithParentId->expects($this->once())->method('getParentItem')->willReturn(true);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->any())->method('sortItemsByPriority')
            ->with([$itemWithParentId], $this->addressMock)
            ->willReturnArgument(0);

        $storeMock = $this->createPartialMock(Store::class, ['getStore', '__wakeup']);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->createMock(Quote::class);

        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithParentId]);
        $totalMock = $this->createMock(Total::class);

        $this->assertInstanceOf(
            Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    public function testCollectItemHasNoChildren()
    {
        $itemWithChildren = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getNoDiscount',
                    'getParentItem',
                    'getHasChildren',
                    'isChildrenCalculated',
                    'getChildren',
                    'getExtensionAttributes',
                    '__wakeup',
                ]
            )
            ->getMock();
        $itemExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->setMethods(['setDiscounts', 'getDiscounts'])->getMock();
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $itemWithChildren->expects(
            $this->any()
        )->method('getExtensionAttributes')->will($this->returnValue($itemExtension));
        $itemWithChildren->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getParentItem')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getHasChildren')->willReturn(false);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemWithChildren], $this->addressMock)
            ->willReturnArgument(0);

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithChildren]);

        $totalMock = $this->createMock(Total::class);
        $this->assertInstanceOf(
            Discount::class,
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

        $quoteMock = $this->createMock(Quote::class);
        $totalMock = $this->createPartialMock(
            Total::class,
            ['getDiscountAmount', 'getDiscountDescription']
        );

        $totalMock->expects($this->once())->method('getDiscountAmount')->willReturn($discountAmount);
        $totalMock->expects($this->once())->method('getDiscountDescription')->willReturn($discountDescription);
        $this->assertEquals($expectedResult, $this->discount->fetch($quoteMock, $totalMock));
    }
}
