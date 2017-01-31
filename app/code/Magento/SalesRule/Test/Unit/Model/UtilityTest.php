<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * Class UtilityTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UtilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $usageFactory;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coupon;

    /**
     * @var \Magento\Quote\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Customer | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Quote\Model\Quote\Address | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $address;

    /**
     * @var \Magento\SalesRule\Model\Rule | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Framework\DataObjectFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $utility;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        $this->usageFactory = $this->getMock(
            'Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->couponFactory = $this->getMock('Magento\SalesRule\Model\CouponFactory', ['create'], [], '', false);
        $this->objectFactory = $this->getMock('Magento\Framework\DataObjectFactory', ['create'], [], '', false);
        $this->customerFactory = $this->getMock(
            'Magento\SalesRule\Model\Rule\CustomerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->coupon = $this->getMock(
            'Magento\SalesRule\Model\Coupon',
            [
                'load',
                'getId',
                'getUsageLimit',
                'getTimesUsed',
                'getUsagePerCustomer',
                '__wakeup'
            ],
            [],
            '',
            false
        );
        $this->quote = $this->getMock('Magento\Quote\Model\Quote', ['__wakeup', 'getStore'], [], '', false);
        $this->customer = $this->getMock(
            'Magento\SalesRule\Model\Rule\Customer',
            ['loadByCustomerRule', '__wakeup'],
            [],
            '',
            false
        );
        $this->rule = $this->getMock(
            'Magento\SalesRule\Model\Rule',
            [
                'hasIsValidForAddress',
                'getIsValidForAddress',
                'setIsValidForAddress',
                '__wakeup',
                'validate',
                'afterLoad',
                'getDiscountQty'
            ],
            [],
            '',
            false
        );
        $this->address = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            [
                'isObjectNew',
                'getQuote',
                'setIsValidForAddress',
                '__wakeup',
                'validate',
                'afterLoad'
            ],
            [],
            '',
            false
        );
        $this->address->setQuote($this->quote);

        $this->item = $this->getMock(
            'Magento\Quote\Model\Quote\Item\AbstractItem',
            [
                'getDiscountCalculationPrice',
                'getCalculationPrice',
                'getBaseDiscountCalculationPrice',
                'getBaseCalculationPrice',
                'getQuote',
                'getAddress',
                'getOptionByCode',
                'getTotalQty',
                '__wakeup'
            ],
            [],
            '',
            false
        );

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();
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
            ->will($this->returnValue(true));
        $this->rule->expects($this->once())
            ->method('getIsValidForAddress')
            ->with($this->address)
            ->will($this->returnValue(true));
        $this->address->expects($this->once())
            ->method('isObjectNew')
            ->will($this->returnValue(false));
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
            ->will($this->returnValue($this->quote));

        $this->coupon->expects($this->atLeastOnce())
            ->method('getUsageLimit')
            ->will($this->returnValue($usageLimit));
        $this->coupon->expects($this->once())
            ->method('getTimesUsed')
            ->will($this->returnValue($timesUsed));
        $this->coupon->expects($this->once())
            ->method('load')
            ->with($couponCode, 'code')
            ->will($this->returnSelf());
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->coupon));
        $this->coupon->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($couponId));
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
            ->will($this->returnValue($this->quote));

        $this->coupon->expects($this->atLeastOnce())
            ->method('getUsagePerCustomer')
            ->will($this->returnValue($usageLimit));
        $this->coupon->expects($this->once())
            ->method('load')
            ->with($couponCode, 'code')
            ->will($this->returnSelf());
        $this->coupon->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($couponId));
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->coupon));

        $couponUsage = new \Magento\Framework\DataObject();
        $this->objectFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($couponUsage));
        $couponUsageModel = $this->getMock('\Magento\SalesRule\Model\ResourceModel\Coupon\Usage', [], [], '', false);
        $couponUsage->setData(['coupon_id' => $couponId, 'times_used' => $timesUsed]);
        $this->usageFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($couponUsageModel));
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
            ->will($this->returnValue($this->quote));
        $this->customer->setId($customerId);
        $this->customer->setTimesUsed($timesUsed);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->customer));

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
            ->will($this->returnValue(true));
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
            ->will($this->returnValue($price));
        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->will($this->returnValue(null));
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
            ->will($this->returnValue(null));
        $this->item->expects($this->any())
            ->method('getBaseCalculationPrice')
            ->will($this->returnValue($calcPrice));
        $this->assertEquals($calcPrice, $this->utility->getItemBasePrice($this->item));
    }

    public function testGetItemQtyMin()
    {
        $qty = 7;
        $discountQty = 4;
        $this->item->expects($this->once())
            ->method('getTotalQty')
            ->will($this->returnValue($qty));
        $this->rule->expects($this->once())
            ->method('getDiscountQty')
            ->will($this->returnValue($discountQty));
        $this->assertEquals(min($discountQty, $qty), $this->utility->getItemQty($this->item, $this->rule));
    }

    public function testGetItemQty()
    {
        $qty = 7;
        $this->item->expects($this->once())
            ->method('getTotalQty')
            ->will($this->returnValue($qty));
        $this->rule->expects($this->once())
            ->method('getDiscountQty')
            ->will($this->returnValue(null));
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
        $discountData = $this->getMock('Magento\SalesRule\Model\Rule\Action\Discount\Data', [], [], '', false);
        $discountData->expects($this->atLeastOnce())
            ->method('getAmount')
            ->will($this->returnValue($amount));
        $discountData->expects($this->atLeastOnce())
            ->method('getBaseAmount')
            ->will($this->returnValue($baseAmount));
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
            ->will($this->returnValue($price));
        $this->item->expects($this->once())
            ->method('getCalculationPrice')
            ->will($this->returnValue($calcPrice));
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
            ->will($this->returnValue($calcPrice));
        $this->item->expects($this->any())
            ->method('getBaseDiscountCalculationPrice')
            ->will($this->returnValue($price));
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
            ->will($this->returnValue($this->quote));

        $store = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $this->priceCurrency->expects($this->any())
            ->method('round')
            ->will($this->returnValueMap([
                        [$discountAmount, $roundedDiscount],
                        [$baseDiscountAmount, $roundedBaseDiscount],
                        [$discountAmount + $delta, $secondRoundedDiscount], //?
                        [$baseDiscountAmount + $baseDelta, $secondRoundedBaseDiscount], //?
                    ]));

        $this->quote->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $this->item->setDiscountPercent($percent);

        $discountData = $this->getMock('Magento\SalesRule\Model\Rule\Action\Discount\Data', [], [], '', false);
        $discountData->expects($this->at(0))
            ->method('getAmount')
            ->will($this->returnValue($discountAmount));
        $discountData->expects($this->at(1))
            ->method('getBaseAmount')
            ->will($this->returnValue($baseDiscountAmount));

        $discountData->expects($this->at(2))
            ->method('setAmount')
            ->with($roundedDiscount);
        $discountData->expects($this->at(3))
            ->method('setBaseAmount')
            ->with($roundedBaseDiscount);

        $discountData->expects($this->at(4))
            ->method('getAmount')
            ->will($this->returnValue($discountAmount));
        $discountData->expects($this->at(5))
            ->method('getBaseAmount')
            ->will($this->returnValue($baseDiscountAmount));

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
