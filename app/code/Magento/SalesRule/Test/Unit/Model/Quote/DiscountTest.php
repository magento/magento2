<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;
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
    use MockCreationTrait;
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

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->discountInterfaceFactoryMock = $this->createMock(RuleDiscountInterfaceFactory::class);
        $this->discountDataInterfaceFactoryMock = $this->createMock(DiscountDataInterfaceFactory::class);
        $this->rulesApplierMock = $this->createMock(RulesApplier::class);
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'canApplyRules',
                    'reset',
                    'initFromQuote',
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
            ->getMock();
        $this->eventManagerMock = $this->createMock(Manager::class);
        $priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnCallback(
                function ($argument) {
                    return round((float) $argument, 2);
                }
            );

        $this->addressMock = $this->createPartialMockWithReflection(
            Address::class,
            [
                'getShippingAmount',
                'getQuote',
                'getAllItems',
                'getExtensionAttributes',
                'getCustomAttributesCodes',
                'setBaseDiscountAmount'
            ]
        );
        $addressExtension = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['setDiscounts', 'getDiscounts']
        );
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

        $shipping = $this->createMock(ShippingInterface::class);
        $shipping->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $this->shippingAssignmentMock = $this->createMock(ShippingAssignmentInterface::class);
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

    /**
     * @return void
     */
    public function testCollectItemNoDiscount(): void
    {
        $itemNoDiscount = $this->createPartialMockWithReflection(
            Item::class,
            ['getNoDiscount', 'getExtensionAttributes', 'getParentItem', 'getId', 'getAddress']
        );
        $itemExtension = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['setDiscounts', 'getDiscounts']
        );
        $itemExtension->method('getDiscounts')->willReturn([]);
        $itemExtension->expects($this->any())
            ->method('setDiscounts')
            ->willReturn([]);
        $itemNoDiscount->expects($this->any())->method('getExtensionAttributes')->willReturn($itemExtension);
        $itemNoDiscount->expects($this->any())->method('getId')->willReturn(1);
        $itemNoDiscount->expects($this->once())->method('getNoDiscount')->willReturn(true);
        $itemNoDiscount->expects($this->once())->method('getAddress')->willReturn($this->addressMock);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemNoDiscount], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);
        $storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['getStore']
        );
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
        $this->addressMock->expects($this->atLeastOnce())->method('setBaseDiscountAmount')->with(0)->willReturnSelf();

        $totalMock = $this->createPartialMockWithReflection(
            Total::class,
            ['getBaseDiscountAmount']
        );
        $totalMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn(0.0);

        $this->assertInstanceOf(
            Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    /**
     * @return void
     */
    public function testCollectItemHasParent(): void
    {
        $itemWithParentId = $this->createPartialMockWithReflection(
            Item::class,
            ['getNoDiscount', 'getParentItem', 'getId', 'getExtensionAttributes', 'getAddress']
        );
        $itemWithParentId->expects($this->once())->method('getNoDiscount')->willReturn(false);
        $itemWithParentId->expects($this->any())->method('getId')->willReturn(1);
        $itemWithParentId->expects($this->any())->method('getParentItem')->willReturn(true);
        $itemWithParentId->expects($this->any())->method('getExtensionAttributes')->willReturn(false);
        $itemWithParentId->expects($this->once())->method('getAddress')->willReturn($this->addressMock);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->any())->method('sortItemsByPriority')
            ->with([$itemWithParentId], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);

        $storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['getStore']
        );
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllAddresses', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$itemWithParentId]);

        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->addressMock->expects($this->atLeastOnce())->method('setBaseDiscountAmount')->with(0)->willReturnSelf();
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithParentId]);
        $totalMock = $this->createPartialMockWithReflection(
            Total::class,
            ['getBaseDiscountAmount']
        );
        $totalMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn(0.0);

        $this->assertInstanceOf(
            Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    /**
     * @return void
     */
    public function testCollectItemHasNoChildren(): void
    {
        $itemWithChildren = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getParentItem',
                'isChildrenCalculated',
                'getChildren',
                'getExtensionAttributes',
                'getId',
                'getAddress',
                'getNoDiscount',
                'getHasChildren',
            ]
        );
        $itemExtension = $this->createPartialMockWithReflection(
            ExtensionAttributesInterface::class,
            ['setDiscounts', 'getDiscounts', 'getId']
        );
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
        $itemWithChildren->expects($this->once())->method('getAddress')->willReturn($this->addressMock);

        $this->validatorMock->expects($this->any())->method('canApplyDiscount')->willReturn(true);
        $this->validatorMock->expects($this->once())->method('sortItemsByPriority')
            ->with([$itemWithChildren], $this->addressMock)
            ->willReturnArgument(0);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$this->rule]);
        $this->rule->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);

        $storeMock = $this->createPartialMockWithReflection(
            Store::class,
            ['getStore']
        );
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['getAllAddresses', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$itemWithChildren]);
        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);
        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(true);
        $this->addressMock->expects($this->atLeastOnce())->method('setBaseDiscountAmount')->with(0)->willReturnSelf();
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$itemWithChildren]);

        $totalMock = $this->createPartialMockWithReflection(
            Total::class,
            ['getBaseDiscountAmount']
        );
        $totalMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn(0.0);

        $this->assertInstanceOf(
            Discount::class,
            $this->discount->collect($quoteMock, $this->shippingAssignmentMock, $totalMock)
        );
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testFetch(): void
    {
        $discountAmount = 100;
        $discountDescription = 100;
        $expectedResult = [
            'code' => 'discount',
            'value' => 100,
            'title' => __('Discount (%1)', $discountDescription)
        ];

        $quoteMock = $this->createMock(Quote::class);
        $totalMock = $this->createPartialMockWithReflection(
            Total::class,
            ['getDiscountAmount', 'getDiscountDescription', 'getBaseDiscountAmount']
        );

        $totalMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn(0.0);
        $totalMock->expects($this->once())->method('getDiscountAmount')->willReturn($discountAmount);
        $totalMock->expects($this->once())->method('getDiscountDescription')->willReturn($discountDescription);
        $this->assertEquals($expectedResult, $this->discount->fetch($quoteMock, $totalMock));
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testCollectAddressBaseDiscountAmountIncludingItemChildren(): void
    {
        $storeId = 1;
        $quote = $this->createMock(Quote::class);
        $quote->expects($this->once())->method('getStoreId')->willReturn($storeId);
        $total = $this->createPartialMockWithReflection(
            Total::class,
            ['getBaseDiscountAmount']
        );
        $total->expects($this->any())->method('getBaseDiscountAmount')->willReturn(-20.00);

        $store = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())->method('getStore')->with($storeId)->willReturn($store);

        $rule1 = $this->createMock(Rule::class);
        $rule1->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);
        $rule2 = $this->createMock(Rule::class);
        $rule2->expects($this->any())->method('getSimpleAction')
            ->willReturn(null);
        $this->validatorMock->expects($this->once())->method('getRules')
            ->with($this->addressMock)
            ->willReturn([$rule1, $rule2]);
        $item = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getNoDiscount',
                'getBaseDiscountAmount',
                'getParentItem',
                'getId',
                'getExtensionAttributes',
                'getAddress',
                'getChildren',
                'isChildrenCalculated'
            ]
        );
        $item->expects($this->any())->method('getNoDiscount')->willReturn(false);
        $item->expects($this->any())->method('getId')->willReturn(1);
        $item->expects($this->any())->method('getParentItem')->willReturn(false);
        $item->expects($this->any())->method('getExtensionAttributes')->willReturn(false);
        $item->expects($this->once())->method('getAddress')->willReturn($this->addressMock);
        $child = $this->createPartialMockWithReflection(
            Item::class,
            ['getBaseDiscountAmount']
        );
        $item->expects($this->any())->method('getChildren')->willReturn([$child]);
        $item->expects($this->any())->method('isChildrenCalculated')->willReturn(true);
        $index = 1;
        $child->expects($this->any())->method('getBaseDiscountAmount')->willReturnCallback(function () use (&$index) {
            $value = $index * 10;
            $index++;
            return $value;
        });
        $this->addressMock->expects($this->any())->method('getAllItems')->willReturn([$item]);
        $this->shippingAssignmentMock->expects($this->any())->method('getItems')->willReturn([$item]);
        $quote->expects($this->any())->method('getAllAddresses')->willReturn([$this->addressMock]);
        $this->validatorMock->expects($this->any())->method('sortItemsByPriority')
            ->with([$item], $this->addressMock)
            ->willReturnArgument(0);

        $this->addressMock->expects($this->exactly(5))
            ->method('setBaseDiscountAmount');

        $this->addressMock->expects(new MethodInvokedAtIndex(0))
            ->method('setBaseDiscountAmount')
            ->with(0);
        $this->addressMock->expects(new MethodInvokedAtIndex(1))
            ->method('setBaseDiscountAmount')
            ->with(0);
        $this->addressMock->expects(new MethodInvokedAtIndex(2))
            ->method('setBaseDiscountAmount')
            ->with(-10);
        $this->addressMock->expects(new MethodInvokedAtIndex(3))
            ->method('setBaseDiscountAmount')
            ->with(-20);
        $this->addressMock->expects(new MethodInvokedAtIndex(4))
            ->method('setBaseDiscountAmount')
            ->with(-20);

        $this->discount->collect($quote, $this->shippingAssignmentMock, $total);
    }
}
