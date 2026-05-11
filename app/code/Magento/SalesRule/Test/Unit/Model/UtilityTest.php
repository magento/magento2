<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Customer;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\ValidateCoupon;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UtilityTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var UsageFactory|MockObject
     */
    protected $usageFactory;

    /**
     * @var CouponFactory|MockObject
     */
    protected $couponFactory;

    /**
     * @var Coupon|MockObject
     */
    protected $coupon;

    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    protected $quote;

    /**
     * @var CustomerFactory|MockObject
     */
    protected $customerFactory;

    /**
     * @var Customer|MockObject
     */
    protected $customer;

    /**
     * @var Address|MockObject
     */
    protected $address;

    /**
     * @var Rule|MockObject
     */
    protected $rule;

    /**
     * @var DataObjectFactory|MockObject
     */
    protected $objectFactory;

    /**
     * @var AbstractItem|MockObject
     */
    protected $item;

    /**
     * @var Utility
     */
    protected $utility;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @var ValidateCoupon|MockObject
     */
    protected $validateCoupon;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->usageFactory = $this->createPartialMock(
            UsageFactory::class,
            ['create']
        );
        $this->couponFactory = $this->createPartialMock(CouponFactory::class, ['create']);
        $this->objectFactory = $this->createPartialMock(DataObjectFactory::class, ['create']);
        $this->customerFactory = $this->createPartialMock(
            CustomerFactory::class,
            ['create']
        );
        $this->coupon = $this->createPartialMock(
            Coupon::class,
            [
                'load',
                'getId',
                'getUsageLimit',
                'getTimesUsed',
                'getUsagePerCustomer'
            ]
        );
        $this->quote = $this->createPartialMock(Quote::class, ['getStore']);
        $this->customer = $this->createPartialMock(
            Customer::class,
            ['loadByCustomerRule']
        );
        $this->rule = $this->createPartialMockWithReflection(
            Rule::class,
            [
                'getDiscountQty',
                'hasIsValidForAddress',
                'getIsValidForAddress',
                'setIsValidForAddress',
                'validate',
                'afterLoad'
            ]
        );
        $this->address = $this->createPartialMockWithReflection(
            Address::class,
            ['setIsValidForAddress', 'isObjectNew', 'getQuote', 'validate', 'afterLoad']
        );
        $this->address->setQuote($this->quote);
        $this->item = $this->createPartialMockWithReflection(
            AbstractItem::class,
            [
                'getDiscountCalculationPrice',
                'getBaseDiscountCalculationPrice',
                'getCalculationPrice',
                'getBaseCalculationPrice',
                'getQuote',
                'getAddress',
                'getOptionByCode',
                'getTotalQty'
            ]
        );

        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $this->validateCoupon = $this->createMock(ValidateCoupon::class);
        $this->utility = new Utility(
            $this->usageFactory,
            $this->couponFactory,
            $this->customerFactory,
            $this->objectFactory,
            $this->priceCurrency,
            $this->validateCoupon
        );
    }

    /**
     * Check rule for specific address
     *
     * @return void
     */
    public function testCanProcessRuleValidAddress(): void
    {
        $this->rule->expects($this->once())
            ->method('hasIsValidForAddress')
            ->with($this->address)
            ->willReturn(true);
        $this->rule->expects($this->once())
            ->method('getIsValidForAddress')
            ->with($this->address)
            ->willReturn(true);
        $this->address->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(false);
        $this->assertTrue($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Check coupon entire usage limit
     *
     * @return void
     */
    public function testCanProcessRuleCouponUsageLimitFail(): void
    {
        $couponCode = 111;
        $quoteId = 4;
        $this->rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $this->quote->setCouponCode($couponCode);
        $this->quote->setId($quoteId);
        $this->address->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->validateCoupon->method('execute')
            ->willReturn(false);

        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Check coupon per customer usage limit
     *
     * @return void
     */
    public function testCanProcessRuleCouponUsagePerCustomerFail(): void
    {
        $couponCode = 111;
        $quoteId = 4;
        $customerId = 1;

        $this->rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $this->quote->setCouponCode($couponCode);
        $this->quote->setId($quoteId);
        $this->quote->setCustomerId($customerId);
        $this->address->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->validateCoupon->method('execute')
            ->willReturn(false);

        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Check rule per customer usage limit
     *
     * @return void
     */
    public function testCanProcessRuleUsagePerCustomer(): void
    {
        $customerId = 1;
        $usageLimit = 1;
        $timesUsed = 2;
        $ruleId = 4;
        $this->rule->setId($ruleId);
        $this->rule->setUsesPerCustomer($usageLimit);
        $this->quote->setCustomerId($customerId);
        $this->address->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->customer->setId($customerId);
        $this->customer->setTimesUsed($timesUsed);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customer);

        $this->validateCoupon->method('execute')
            ->willReturn(true);

        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Quote does not meet rule's conditions
     *
     * @return void
     */
    public function testCanProcessRuleInvalidConditions(): void
    {
        $this->address->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Quote does not meet rule's conditions
     *
     * @return void
     */
    public function testCanProcessRule(): void
    {
        $this->rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $this->rule->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->address->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->validateCoupon->method('execute')
            ->willReturn(true);
        $this->assertTrue($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * When rule has item-level actions (e.g. exclude SKU), subtotal condition is evaluated
     * against eligible items only. Rule validation sees the eligible-only totals and
     * canProcessRule returns the correct result (e.g. false when condition fails).
     *
     * @return void
     */
    public function testCanProcessRuleUsesEligibleItemsSubtotalWhenRuleHasItemRestrictions(): void
    {
        $actionsCombine = $this->createMock(RuleCombine::class);
        $actionsCombine->method('getConditions')->willReturn([1]);

        $rule = $this->createPartialMock(Rule::class, [
            'getActions', 'validate', 'hasIsValidForAddress', 'getIsValidForAddress',
            'setIsValidForAddress', 'afterLoad'
        ]);
        $rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $rule->method('hasIsValidForAddress')->willReturn(false);
        $rule->method('getActions')->willReturn($actionsCombine);
        $rule->method('validate')->willReturn(false);
        $rule->method('afterLoad');

        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();
        $address->method('getQuote')->willReturn($this->quote);
        $address->method('getAllItems')->willReturn([]);

        $this->validateCoupon->method('execute')->willReturn(true);

        $this->assertFalse($this->utility->canProcessRule($rule, $address));
    }

    /**
     * When actions are not a Rule Combine, ruleHasItemRestrictions is false.
     *
     * @return void
     */
    public function testCanProcessRuleWhenActionsAreNotCombineSkipsEligibleItemTotals(): void
    {
        $rule = $this->createPartialMock(Rule::class, [
            'getActions',
            'validate',
            'hasIsValidForAddress',
            'getIsValidForAddress',
            'setIsValidForAddress',
            'afterLoad',
        ]);
        $rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $rule->method('hasIsValidForAddress')->willReturn(false);
        $rule->method('getActions')->willReturn(new \stdClass());
        $rule->method('validate')->willReturn(true);
        $rule->method('afterLoad');
        $rule->expects($this->once())
            ->method('setIsValidForAddress')
            ->with($this->isInstanceOf(Address::class), true);

        $this->address->method('getQuote')->willReturn($this->quote);
        $this->validateCoupon->method('execute')->willReturn(true);

        $this->assertTrue($this->utility->canProcessRule($rule, $this->address));
    }

    /**
     * Covers eligible-only Eligible Items Totals on Address loop and for Rule Totals.
     *
     * @return void
     */
    public function testCanProcessRuleEligibleLineItemTotalsExcludeIneligibleAndRestoreAddress(): void
    {
        $fixtures = $this->buildEligibleItemTotalsCanProcessRuleFixtures();
        $addressState = $this->defaultEligibleTotalsAddressState();
        $address = $this->createAddressMockWithTrackedTotals($addressState, $fixtures['lineItems']);

        $this->validateCoupon->method('execute')->willReturn(true);

        $this->assertTrue($this->utility->canProcessRule($fixtures['rule'], $address));

        $this->assertSame(500.0, $addressState['base_subtotal']);
        $this->assertSame(490.0, $addressState['base_subtotal_with_discount']);
        $this->assertSame(550.0, $addressState['base_subtotal_total_incl_tax']);
        $this->assertSame(10.0, $addressState['total_qty']);
        $this->assertSame(20.0, $addressState['weight']);
    }

    /**
     * @return array<string, float>
     */
    private function defaultEligibleTotalsAddressState(): array
    {
        return [
            'base_subtotal' => 500.0,
            'base_subtotal_with_discount' => 490.0,
            'base_subtotal_total_incl_tax' => 550.0,
            'total_qty' => 10.0,
            'weight' => 20.0,
        ];
    }

    /**
     * @param array<string, float> $addressState
     * @param list<AbstractItem|MockObject> $lineItems
     * @return Address&MockObject
     */
    private function createAddressMockWithTrackedTotals(array &$addressState, array $lineItems): MockObject
    {
        $address = $this->createPartialMockWithReflection(Address::class, [
            'isObjectNew',
            'getQuote',
            'getAllItems',
            'getBaseSubtotal',
            'setBaseSubtotal',
            'getBaseSubtotalWithDiscount',
            'setBaseSubtotalWithDiscount',
            'getBaseSubtotalTotalInclTax',
            'setBaseSubtotalTotalInclTax',
            'getTotalQty',
            'setTotalQty',
            'getWeight',
            'setWeight',
        ]);
        $address->method('isObjectNew')->willReturn(false);
        $address->method('getQuote')->willReturn($this->quote);
        $address->method('getAllItems')->willReturn($lineItems);
        $this->stubAddressQuoteTotalsOnMock($address, $addressState);

        return $address;
    }

    /**
     * @param array<string, float> $addressState
     */
    private function stubAddressQuoteTotalsOnMock(MockObject $address, array &$addressState): void
    {
        $pairs = [
            ['getBaseSubtotal', 'setBaseSubtotal', 'base_subtotal'],
            ['getBaseSubtotalWithDiscount', 'setBaseSubtotalWithDiscount', 'base_subtotal_with_discount'],
            ['getBaseSubtotalTotalInclTax', 'setBaseSubtotalTotalInclTax', 'base_subtotal_total_incl_tax'],
            ['getTotalQty', 'setTotalQty', 'total_qty'],
            ['getWeight', 'setWeight', 'weight'],
        ];
        foreach ($pairs as [$getter, $setter, $key]) {
            $address->method($getter)->willReturnCallback(
                function () use (&$addressState, $key): float {
                    return $addressState[$key];
                }
            );
            $address->method($setter)->willReturnCallback(
                function ($v) use (&$addressState, $key): void {
                    $addressState[$key] = (float) $v;
                }
            );
        }
    }

    /**
     * @return array{rule: Rule&MockObject, lineItems: list<AbstractItem|MockObject>}
     */
    private function buildEligibleItemTotalsCanProcessRuleFixtures(): array
    {
        $itemEligible = $this->eligibleTotalsItemStub([
            'getParentItem' => null,
            'getHasChildren' => false,
            'getChildren' => [],
            'isChildrenCalculated' => false,
            'getNoDiscount' => false,
            'getBaseRowTotal' => 100.0,
            'getBaseRowTotalInclTax' => 110.0,
            'getQty' => 2.0,
            'getRowWeight' => 3.0,
        ]);

        $actionsCombine = $this->createMock(RuleCombine::class);
        $actionsCombine->method('getConditions')->willReturn([$this->createStub(AbstractCondition::class)]);
        $actionsCombine->method('validate')->willReturnCallback(
            fn ($item): bool => $item === $itemEligible
        );

        $configurableParent = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductType'])
            ->getMock();
        $configurableParent->method('getProductType')->willReturn('configurable');

        $lineItems = [
            $this->eligibleTotalsItemStub([
                'getParentItem' => $configurableParent,
                'getHasChildren' => false,
                'getChildren' => [],
                'isChildrenCalculated' => false,
                'getNoDiscount' => false,
            ]),
            $this->eligibleTotalsItemStub([
                'getParentItem' => null,
                'getHasChildren' => false,
                'getChildren' => [],
                'isChildrenCalculated' => false,
                'getNoDiscount' => true,
            ]),
            $this->eligibleTotalsItemStub([
                'getParentItem' => null,
                'getHasChildren' => true,
                'getChildren' => [$this->createStub(AbstractItem::class)],
                'isChildrenCalculated' => true,
                'getNoDiscount' => false,
            ]),
            $this->eligibleTotalsItemStub([
                'getParentItem' => null,
                'getHasChildren' => false,
                'getChildren' => [],
                'isChildrenCalculated' => false,
                'getNoDiscount' => false,
            ]),
            $itemEligible,
        ];

        $rule = $this->createPartialMock(Rule::class, [
            'getActions',
            'validate',
            'hasIsValidForAddress',
            'getIsValidForAddress',
            'setIsValidForAddress',
            'afterLoad',
        ]);
        $rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $rule->method('hasIsValidForAddress')->willReturn(false);
        $rule->method('getActions')->willReturn($actionsCombine);
        $rule->method('validate')->willReturn(true);
        $rule->method('afterLoad');
        $rule->expects($this->once())
            ->method('setIsValidForAddress')
            ->with($this->isInstanceOf(Address::class), true);

        return ['rule' => $rule, 'lineItems' => $lineItems];
    }

    /**
     * @param array<string, mixed> $stubs method name => return value
     * @return AbstractItem&MockObject
     */
    private function eligibleTotalsItemStub(array $stubs): MockObject
    {
        $stubs = array_merge(
            [
                'getQuote' => $this->quote,
                'getAddress' => null,
                'getOptionByCode' => null,
            ],
            $stubs
        );

        /** @var AbstractItem&MockObject $item */
        $item = $this->createPartialMockWithReflection(AbstractItem::class, [
            'getParentItem',
            'getChildren',
            'isChildrenCalculated',
            'getQty',
            'getHasChildren',
            'getNoDiscount',
            'getBaseRowTotal',
            'getBaseRowTotalInclTax',
            'getRowWeight',
            'getQuote',
            'getAddress',
            'getOptionByCode',
        ]);
        foreach ($stubs as $method => $value) {
            if ($value instanceof \Closure) {
                $item->method($method)->willReturnCallback($value);
            } else {
                $item->method($method)->willReturn($value);
            }
        }

        return $item;
    }

    /**
     * @return void
     */
    #[DataProvider('deltaRoundingFixHundredPercentDataProvider')]
    public function testDeltaRoundingFixHundredPercentClampsNegativeDeltas(
        float $rowTotalInclTax,
        float $baseRowTotalInclTax,
        float $discountAmount,
        float $baseDiscountAmount,
        float $expectedAmount,
        float $expectedBaseAmount
    ): void {
        $item = $this->createPartialMockWithReflection(AbstractItem::class, [
            'getDiscountPercent',
            'getRowTotal',
            'getRowTotalInclTax',
            'getBaseRowTotalInclTax',
            'getQuote',
            'getAddress',
            'getOptionByCode',
        ]);
        $item->method('getAddress')->willReturn(null);
        $item->method('getOptionByCode')->willReturn(null);
        $item->method('getDiscountPercent')->willReturn(100);
        $item->method('getRowTotal')->willReturn(0.0);
        $item->method('getRowTotalInclTax')->willReturn($rowTotalInclTax);
        $item->method('getBaseRowTotalInclTax')->willReturn($baseRowTotalInclTax);
        $item->method('getQuote')->willReturn($this->quote);

        $discountData = $this->createMock(Data::class);
        $discountData->method('getAmount')->willReturn($discountAmount);
        $discountData->method('getBaseAmount')->willReturn($baseDiscountAmount);

        $this->priceCurrency->method('round')->willReturnCallback(
            static fn (float $amount): float => round($amount, 4)
        );

        $discountData->expects($this->once())
            ->method('setAmount')
            ->with($expectedAmount);
        $discountData->expects($this->once())
            ->method('setBaseAmount')
            ->with($expectedBaseAmount);

        $this->assertSame($this->utility, $this->utility->deltaRoundingFix($discountData, $item));
    }

    /**
     * @return array<string, array{float, float, float, float, float, float}>
     */
    public static function deltaRoundingFixHundredPercentDataProvider(): array
    {
        return [
            'both_discount_and_base_clamped' => [100.0, 100.0, 120.0, 130.0, 100.0, 100.0],
            'only_discount_clamped' => [80.0, 200.0, 100.0, 50.0, 80.0, 50.0],
            'only_base_clamped' => [200.0, 90.0, 50.0, 100.0, 50.0, 90.0],
        ];
    }

    /**
     * @return void
     */
    public function testGetItemPrice(): void
    {
        $price = $this->getItemPrice();
        $this->assertEquals($price, $this->utility->getItemPrice($this->item));
    }

    /**
     * @return void
     */
    public function testGetItemPriceNull(): void
    {
        $price = 4;

        $this->item->expects($this->once())
            ->method('getDiscountCalculationPrice')
            ->willReturn($price);
        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn(null);
        $this->assertEquals($price, $this->utility->getItemPrice($this->item));
    }

    /**
     * @return void
     */
    public function testGetItemBasePrice(): void
    {
        $price = $this->getItemBasePrice();
        $this->assertEquals($price, $this->utility->getItemBasePrice($this->item));
    }

    /**
     * @return void
     */
    public function testGetBaseItemPriceCalculation(): void
    {
        $calcPrice = 5;
        $this->item->expects($this->once())
            ->method('getDiscountCalculationPrice')
            ->willReturn(null);
        $this->item->expects($this->any())
            ->method('getBaseCalculationPrice')
            ->willReturn($calcPrice);
        $this->assertEquals($calcPrice, $this->utility->getItemBasePrice($this->item));
    }

    /**
     * @return void
     */
    public function testGetItemQtyMin(): void
    {
        $qty = 7;
        $discountQty = 4;
        $this->item->expects($this->once())
            ->method('getTotalQty')
            ->willReturn($qty);
        $this->rule->expects($this->once())
            ->method('getDiscountQty')
            ->willReturn($discountQty);
        $this->assertEquals(min($discountQty, $qty), $this->utility->getItemQty($this->item, $this->rule));
    }

    /**
     * @return void
     */
    public function testGetItemQty(): void
    {
        $qty = 7;
        $this->item->expects($this->once())
            ->method('getTotalQty')
            ->willReturn($qty);
        $this->rule->expects($this->once())
            ->method('getDiscountQty')
            ->willReturn(null);
        $this->assertEquals($qty, $this->utility->getItemQty($this->item, $this->rule));
    }

    /**
     * @param mixed $a1
     * @param mixed $a2
     * @param bool $isSting
     * @param mixed $expected
     *
     * @return void
     */
    #[DataProvider('mergeIdsDataProvider')]
    public function testMergeIds($a1, $a2, bool $isSting, $expected): void
    {
        $this->assertEquals($expected, $this->utility->mergeIds($a1, $a2, $isSting));
    }

    /**
     * @return array
     */
    public static function mergeIdsDataProvider(): array
    {
        return [
            ['id1,id2', '', true, 'id1,id2'],
            ['id1,id2', '', false, ['id1', 'id2']],
            ['', 'id3,id4', false, ['id3', 'id4']],
            ['', 'id3,id4', true, 'id3,id4'],
            [['id1', 'id2'], ['id3', 'id4'], false, ['id1', 'id2', 'id3', 'id4']],
            [['id1', 'id2'], ['id3', 'id4'], true, 'id1,id2,id3,id4']
        ];
    }

    /**
     * @return void
     */
    public function testMinFix(): void
    {
        $qty = 13;
        $amount = 10;
        $baseAmount = 12;
        $fixedAmount = 20;
        $fixedBaseAmount = 24;
        $this->getItemPrice();
        $this->getItemBasePrice();
        $this->item->setDiscountAmount($amount);
        $this->item->setBaseDiscountAmount($baseAmount);
        $this->item->setQty($qty);
        $discountData = $this->createMock(Data::class);
        $discountData->expects($this->atLeastOnce())
            ->method('getAmount')
            ->willReturn($amount);
        $discountData->expects($this->atLeastOnce())
            ->method('getBaseAmount')
            ->willReturn($baseAmount);
        $discountData->expects($this->once())
            ->method('setAmount')
            ->with($fixedAmount);
        $discountData->expects($this->once())
            ->method('setBaseAmount')
            ->with($fixedBaseAmount);

        $this->assertNull($this->utility->minFix($discountData, $this->item, $qty));
    }

    /**
     * @return int
     */
    protected function getItemPrice(): int
    {
        $price = 4;
        $calcPrice = 5;

        $this->item->expects($this->atLeastOnce())
            ->method('getDiscountCalculationPrice')
            ->willReturn($price);
        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->willReturn($calcPrice);
        return $price;
    }

    /**
     * @return int
     */
    protected function getItemBasePrice(): int
    {
        $price = 4;
        $calcPrice = 5;
        $this->item->expects($this->atLeastOnce())
            ->method('getDiscountCalculationPrice')
            ->willReturn($calcPrice);
        $this->item->expects($this->any())
            ->method('getBaseDiscountCalculationPrice')
            ->willReturn($price);
        return $price;
    }

    /**
     * @param $discountAmount
     * @param $baseDiscountAmount
     * @param $percent
     * @param $rowTotal
     * @return void
     */
    #[DataProvider('deltaRoundingFixDataProvider')]
    public function testDeltaRoundignFix($discountAmount, $baseDiscountAmount, $percent, $rowTotal): void
    {
        $roundedDiscount = round($discountAmount, 2);
        $roundedBaseDiscount = round($baseDiscountAmount, 2);
        $delta = $discountAmount - $roundedDiscount;
        $baseDelta = $baseDiscountAmount - $roundedBaseDiscount;
        $secondRoundedDiscount = round($discountAmount + $delta);
        $secondRoundedBaseDiscount = round($baseDiscountAmount + $baseDelta);

        $this->item->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $store = $this->createMock(Store::class);
        $this->priceCurrency->expects($this->any())
            ->method('round')
            ->willReturnMap(
                [
                    [$discountAmount, $roundedDiscount],
                    [$baseDiscountAmount, $roundedBaseDiscount],
                    [$discountAmount + $delta, $secondRoundedDiscount], //?
                    [$baseDiscountAmount + $baseDelta, $secondRoundedBaseDiscount] //?
                ]
            );

        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->item->setDiscountPercent($percent);
        $this->item->setRowTotal($rowTotal);

        $discountData = $this->createMock(Data::class);

        $discountData->method('getAmount')
            ->willReturnOnConsecutiveCalls($discountAmount, $discountAmount);
        $discountData->method('setBaseAmount')
            ->willReturnCallback(function ($arg1) use ($roundedBaseDiscount, $secondRoundedBaseDiscount) {
                if ($arg1 == $roundedBaseDiscount || $arg1 == $secondRoundedBaseDiscount) {
                    return null;
                }
            });
        $discountData->method('setAmount')
            ->willReturnCallback(function ($arg1) use ($roundedDiscount, $secondRoundedDiscount) {
                if ($arg1 == $roundedDiscount || $arg1 == $secondRoundedDiscount) {
                    return null;
                }
            });
        $discountData->method('getBaseAmount')
            ->willReturnOnConsecutiveCalls($baseDiscountAmount, $baseDiscountAmount);

        $this->assertEquals($this->utility, $this->utility->deltaRoundingFix($discountData, $this->item));
    }

    public static function deltaRoundingFixDataProvider()
    {
        return [
            ['discountAmount' => 10.003, 'baseDiscountAmount' => 12.465, 'percent' => 15, 'rowTotal' => 100],
            ['discountAmount' => 5.0015, 'baseDiscountAmount' => 6.2325, 'percent' => 7.5, 'rowTotal' => 100],
        ];
    }

    /**
     * @return void
     */
    public function testResetRoundingDeltas(): void
    {
        $this->assertNull($this->utility->resetRoundingDeltas());
    }
}
