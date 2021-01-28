<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * Class UtilityTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UtilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $usageFactory;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $coupon;

    /**
     * @var \Magento\Quote\Model\Quote | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quote;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Customer | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Quote\Model\Quote\Address | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $address;

    /**
     * @var \Magento\SalesRule\Model\Rule | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Framework\DataObjectFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $item;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $utility;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $this->usageFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory::class,
            ['create']
        );
        $this->couponFactory = $this->createPartialMock(\Magento\SalesRule\Model\CouponFactory::class, ['create']);
        $this->objectFactory = $this->createPartialMock(\Magento\Framework\DataObjectFactory::class, ['create']);
        $this->customerFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\CustomerFactory::class,
            ['create']
        );
        $this->coupon = $this->createPartialMock(
            \Magento\SalesRule\Model\Coupon::class,
            [
                'load',
                'getId',
                'getUsageLimit',
                'getTimesUsed',
                'getUsagePerCustomer',
                '__wakeup'
            ]
        );
        $this->quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['__wakeup', 'getStore']);
        $this->customer = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Customer::class,
            ['loadByCustomerRule', '__wakeup']
        );
        $this->rule = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, [
                'hasIsValidForAddress',
                'getIsValidForAddress',
                'setIsValidForAddress',
                '__wakeup',
                'validate',
                'afterLoad',
                'getDiscountQty'
            ]);
        $this->address = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, [
                'isObjectNew',
                'getQuote',
                'setIsValidForAddress',
                '__wakeup',
                'validate',
                'afterLoad'
            ]);
        $this->address->setQuote($this->quote);
        $this->item = $this->createPartialMock(\Magento\Quote\Model\Quote\Item\AbstractItem::class, [
                'getDiscountCalculationPrice',
                'getCalculationPrice',
                'getBaseDiscountCalculationPrice',
                'getBaseCalculationPrice',
                'getQuote',
                'getAddress',
                'getOptionByCode',
                'getTotalQty',
                '__wakeup'
            ]);

        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMock();
        $this->utility = new \Magento\SalesRule\Model\Utility(
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
        $this->rule->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC);
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
            ->with($couponCode, 'code')
            ->willReturnSelf();
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

        $this->rule->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC);
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
            ->with($couponCode, 'code')
            ->willReturnSelf();
        $this->coupon->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($couponId);
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $couponUsage = new \Magento\Framework\DataObject();
        $this->objectFactory->expects($this->once())
            ->method('create')
            ->willReturn($couponUsage);
        $couponUsageModel = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Usage::class);
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
        $this->rule->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON);
        $this->assertFalse($this->utility->canProcessRule($this->rule, $this->address));
    }

    /**
     * Quote does not meet rule's conditions
     */
    public function testCanProcessRule()
    {
        $this->rule->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON);
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
        $discountData = $this->createMock(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class);
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

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $this->priceCurrency->expects($this->any())
            ->method('round')
            ->willReturnMap([
                        [$discountAmount, $roundedDiscount],
                        [$baseDiscountAmount, $roundedBaseDiscount],
                        [$discountAmount + $delta, $secondRoundedDiscount], //?
                        [$baseDiscountAmount + $baseDelta, $secondRoundedBaseDiscount], //?
                    ]);

        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($store);

        $this->item->setDiscountPercent($percent);

        $discountData = $this->createMock(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class);
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
