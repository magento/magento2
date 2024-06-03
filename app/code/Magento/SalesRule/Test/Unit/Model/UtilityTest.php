<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Customer;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\Utility;
use Magento\SalesRule\Model\ValidateCoupon;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UtilityTest extends TestCase
{
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
        $this->rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getDiscountQty'])
            ->onlyMethods(
                [
                    'hasIsValidForAddress',
                    'getIsValidForAddress',
                    'setIsValidForAddress',
                    'validate',
                    'afterLoad'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->address = $this->getMockBuilder(Address::class)
            ->addMethods(['setIsValidForAddress'])
            ->onlyMethods(['isObjectNew', 'getQuote', 'validate', 'afterLoad'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->address->setQuote($this->quote);
        $this->item = $this->getMockBuilder(AbstractItem::class)
            ->addMethods(['getDiscountCalculationPrice', 'getBaseDiscountCalculationPrice'])
            ->onlyMethods(
                [
                    'getCalculationPrice',
                    'getBaseCalculationPrice',
                    'getQuote',
                    'getAddress',
                    'getOptionByCode',
                    'getTotalQty'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder(PriceCurrencyInterface::class)
            ->getMock();
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
     * @dataProvider mergeIdsDataProvider
     */
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
     * @dataProvider deltaRoundingFixDataProvider
     * @param $discountAmount
     * @param $baseDiscountAmount
     * @param $percent
     * @param $rowTotal
     * @return void
     */
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
