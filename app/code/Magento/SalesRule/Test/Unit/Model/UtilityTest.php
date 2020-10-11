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
        $this->utility = new Utility(
            $this->usageFactory,
            $this->couponFactory,
            $this->customerFactory,
            $this->objectFactory,
            $this->priceCurrency
        );
    }

    /**
     * Check rule for specific address
     */
    public function testCanProcessRuleValidAddress()
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
     */
    public function testCanProcessRuleCouponUsageLimitFail()
    {
        $couponCode = 111;
        $couponId = 4;
        $quoteId = 4;
        $usageLimit = 1;
        $timesUsed = 2;
        $this->rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $this->quote->setCouponCode($couponCode);
        $this->quote->setId($quoteId);
        $this->address->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->coupon->expects($this->atLeastOnce())
            ->method('getUsageLimit')
            ->willReturn($usageLimit);
        $this->coupon->expects($this->once())
            ->method('getTimesUsed')
            ->willReturn($timesUsed);
        $this->coupon->expects($this->once())
            ->method('load')
            ->with($couponCode, 'code')->willReturnSelf();
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);
        $this->coupon->expects($this->once())
            ->method('getId')
            ->willReturn($couponId);
        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Check coupon per customer usage limit
     */
    public function testCanProcessRuleCouponUsagePerCustomerFail()
    {
        $couponCode = 111;
        $couponId = 4;
        $quoteId = 4;
        $customerId = 1;
        $usageLimit = 1;
        $timesUsed = 2;

        $this->rule->setCouponType(Rule::COUPON_TYPE_SPECIFIC);
        $this->quote->setCouponCode($couponCode);
        $this->quote->setId($quoteId);
        $this->quote->setCustomerId($customerId);
        $this->address->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->coupon->expects($this->atLeastOnce())
            ->method('getUsagePerCustomer')
            ->willReturn($usageLimit);
        $this->coupon->expects($this->once())
            ->method('load')
            ->with($couponCode, 'code')->willReturnSelf();
        $this->coupon->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($couponId);
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $couponUsage = new DataObject();
        $this->objectFactory->expects($this->once())
            ->method('create')
            ->willReturn($couponUsage);
        $couponUsageModel = $this->createMock(Usage::class);
        $couponUsage->setData(['coupon_id' => $couponId, 'times_used' => $timesUsed]);
        $this->usageFactory->expects($this->once())
            ->method('create')
            ->willReturn($couponUsageModel);
        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Check rule per customer usage limit
     */
    public function testCanProcessRuleUsagePerCustomer()
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

        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Quote does not meet rule's conditions
     */
    public function testCanProcessRuleInvalidConditions()
    {
        $this->rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Quote does not meet rule's conditions
     */
    public function testCanProcessRule()
    {
        $this->rule->setCouponType(Rule::COUPON_TYPE_NO_COUPON);
        $this->rule->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->assertTrue($this->utility->canProcessRule($this->rule, $this->address));
    }

    public function testGetItemPrice()
    {
        $price = $this->getItemPrice();
        $this->assertEquals($price, $this->utility->getItemPrice($this->item));
    }

    public function testGetItemPriceNull()
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

    public function testGetItemBasePrice()
    {
        $price = $this->getItemBasePrice();
        $this->assertEquals($price, $this->utility->getItemBasePrice($this->item));
    }

    public function testGetBaseItemPriceCalculation()
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

    public function testGetItemQtyMin()
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

    public function testGetItemQty()
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
     * @dataProvider mergeIdsDataProvider
     *
     * @param [] $a1
     * @param [] $a2
     * @param bool $isSting
     * @param [] $expected
     */
    public function testMergeIds($a1, $a2, $isSting, $expected)
    {
        $this->assertEquals($expected, $this->utility->mergeIds($a1, $a2, $isSting));
    }

    /**
     * @return array
     */
    public function mergeIdsDataProvider()
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

    public function testMinFix()
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
    protected function getItemPrice()
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
    protected function getItemBasePrice()
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

    public function testDeltaRoundignFix()
    {
        $discountAmount = 10.003;
        $baseDiscountAmount = 12.465;
        $percent = 15;
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
                    [$baseDiscountAmount + $baseDelta, $secondRoundedBaseDiscount], //?
                ]
            );

        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->item->setDiscountPercent($percent);

        $discountData = $this->createMock(Data::class);
        $discountData->expects($this->at(0))
            ->method('getAmount')
            ->willReturn($discountAmount);
        $discountData->expects($this->at(1))
            ->method('getBaseAmount')
            ->willReturn($baseDiscountAmount);

        $discountData->expects($this->at(2))
            ->method('setAmount')
            ->with($roundedDiscount);
        $discountData->expects($this->at(3))
            ->method('setBaseAmount')
            ->with($roundedBaseDiscount);

        $discountData->expects($this->at(4))
            ->method('getAmount')
            ->willReturn($discountAmount);
        $discountData->expects($this->at(5))
            ->method('getBaseAmount')
            ->willReturn($baseDiscountAmount);

        $discountData->expects($this->at(6))
            ->method('setAmount')
            ->with($secondRoundedDiscount);
        $discountData->expects($this->at(7))
            ->method('setBaseAmount')
            ->with($secondRoundedBaseDiscount);

        $this->assertEquals($this->utility, $this->utility->deltaRoundingFix($discountData, $this->item));
        $this->assertEquals($this->utility, $this->utility->deltaRoundingFix($discountData, $this->item));
    }

    public function testResetRoundingDeltas()
    {
        $this->assertNull($this->utility->resetRoundingDeltas());
    }
}
