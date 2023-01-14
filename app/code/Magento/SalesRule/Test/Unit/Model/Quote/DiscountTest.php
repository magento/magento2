<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Quote\Discount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\Store;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends TestCase
{
    /**
     * @var Discount
     */
    protected $discount;

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

    /**
     * @var Rule|MockObject
     */
    private $rule;

    /**
     * @var RuleDiscountInterfaceFactory|MockObject
     */
    private $discountInterfaceFactoryMock;

    /**
     * @var DiscountDataInterfaceFactory|MockObject
     */
    private $discountDataInterfaceFactoryMock;

    /**
     * @var RulesApplier|MockObject
     */
    private $rulesApplierMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->discountInterfaceFactoryMock = $this->createMock(RuleDiscountInterfaceFactory::class);
        $this->discountDataInterfaceFactoryMock = $this->createMock(DiscountDataInterfaceFactory::class);
        $this->rulesApplierMock = $this->createMock(RulesApplier::class);
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
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
                    'getRules',
                    'prepareDescription'
                ]
            )
            ->getMock();
        $this->rule = $this->getMockBuilder(Rule::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'getSimpleAction'
                ]
            )
            ->getMock();
        $this->eventManagerMock = $this->createMock(Manager::class);
        $priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnCallback(
                function ($argument) {
                    return round((float) $argument, 2);
                }
            );

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(['getShippingAmount'])
            ->onlyMethods(['getQuote', 'getAllItems', 'getExtensionAttributes', 'getCustomAttributesCodes'])
            ->disableOriginalConstructor()
            ->getMock();
        $addressExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->addMethods(['setDiscounts', 'getDiscounts'])->getMockForAbstractClass();
        $addressExtension->method('getDiscounts')->willReturn([]);
        $addressExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $this->addressMock->expects(
            $this->any()
        )->method('getExtensionAttributes')->willReturn($addressExtension);
        $this->addressMock->expects($this->any())
            ->method('getCustomAttributesCodes')
            ->willReturn([]);

        $shipping = $this->getMockForAbstractClass(ShippingInterface::class);
        $shipping->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $this->shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $this->shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shipping);
        $this->discountFactory = $this->createPartialMock(
            DataFactory::class,
            ['create']
        );

        /** @var Discount $discount */
        $this->discount = new Discount(
            $this->eventManagerMock,
            $this->storeManagerMock,
            $this->validatorMock,
            $priceCurrencyMock,
            $this->discountInterfaceFactoryMock,
            $this->discountDataInterfaceFactoryMock,
            $this->rulesApplierMock
        );
        $discountData = $this->getMockBuilder(Data::class)
            ->getMock();
        $this->discountFactory->expects($this->any())
            ->method('create')
            ->with($this->anything())
            ->willReturn($discountData);
    }

    public function testCollectItemNoDiscount()
    {
        $itemNoDiscount = $this->getMockBuilder(Item::class)
            ->addMethods(['getNoDiscount'])
            ->onlyMethods(['getExtensionAttributes', 'getParentItem', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->addMethods(['setDiscounts', 'getDiscounts'])->getMockForAbstractClass();
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $itemNoDiscount->expects($this->any())->method('getExtensionAttributes')->willReturn($itemExtension);
        $itemNoDiscount->expects($this->any())->method('getId')->willReturn(1);
        $itemNoDiscount->expects($this->once())->method('getNoDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemNoDiscount], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);
        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllAddresses', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$itemNoDiscount]);
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
        $itemWithParentId = $this->getMockBuilder(Item::class)
            ->addMethods(['getNoDiscount'])
            ->onlyMethods(['getParentItem', 'getId', 'getExtensionAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemWithParentId->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithParentId->expects($this->any())->method('getId')->willReturn(1);
        $itemWithParentId->expects($this->any())->method('getParentItem')->willReturn(true);
        $itemWithParentId->expects($this->any())->method('getExtensionAttributes')->willReturn(false);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->any())->method('sortItemsByPriority')
            ->with([$itemWithParentId], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllAddresses', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$itemWithParentId]);

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
            ->onlyMethods(
                [
                    'getParentItem',
                    'isChildrenCalculated',
                    'getChildren',
                    'getExtensionAttributes',
                    'getId',
                ]
            )->addMethods(
                [
                    'getNoDiscount',
                    'getHasChildren',
                ]
            )
            ->getMock();
        $itemExtension = $this->getMockBuilder(
            ExtensionAttributesInterface::class
        )->addMethods(['setDiscounts', 'getDiscounts', 'getId'])->getMock();
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $itemExtension->expects($this->any())->method('getId')->willReturn(1);
        $itemWithChildren->expects(
            $this->any()
        )->method('getExtensionAttributes')->willReturn($itemExtension);
        $itemWithChildren->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithChildren->expects($this->any())->method('getParentItem')->willReturn(false);
        $itemWithChildren->expects($this->once())->method('getHasChildren')->willReturn(false);
        $itemWithChildren->expects($this->any())->method('getId')->willReturn(2);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemWithChildren], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllAddresses', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$itemWithChildren]);
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
        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getDiscountAmount', 'getDiscountDescription'])
            ->disableOriginalConstructor()
            ->getMock();

        $totalMock->expects($this->once())->method('getDiscountAmount')->willReturn($discountAmount);
        $totalMock->expects($this->once())->method('getDiscountDescription')->willReturn($discountDescription);
        $this->assertEquals($expectedResult, $this->discount->fetch($quoteMock, $totalMock));
    }
}
