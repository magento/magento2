<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Coupon\Usage;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Coupon\Usage\Processor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Customer;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var RuleFactory
     */
    private $ruleFactoryMock;

    /**
     * @var CustomerFactory
     */
    private $ruleCustomerFactoryMock;

    /**
     * @var Coupon
     */
    private $couponMock;

    /**
     * @var Usage
     */
    private $couponUsageMock;

    /**
     * @var UpdateInfo
     */
    private $updateInfoMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->getMockBuilder(RuleFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->ruleCustomerFactoryMock = $this->getMockBuilder(CustomerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->couponMock = $this->getMockBuilder(Coupon::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->couponUsageMock = $this->getMockBuilder(Usage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateInfoMock = $this->getMockBuilder(UpdateInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = (new ObjectManager($this))->getObject(
            Processor::class,
            [
                'ruleFactory' => $this->ruleFactoryMock,
                'ruleCustomerFactory' => $this->ruleCustomerFactoryMock,
                'coupon' => $this->couponMock,
                'couponUsage' => $this->couponUsageMock
            ]
        );
    }

    /**
     * Test to update coupon usage
     *
     * @param $isIncrement
     * @param $timesUsed
     *
     * @return void
     * @dataProvider dataProvider
     */
    public function testProcess($isIncrement, $timesUsed): void
    {
        $ruleId = 1;
        $customerId = 256;
        $couponId = 1;
        $couponCode = 'DISCOUNT-10';
        $setTimesUsed = $timesUsed + ($isIncrement ? 1 : -1);
        $ruleCustomerId = 13;

        $this->updateInfoMock->expects($this->exactly(2))->method('getAppliedRuleIds')->willReturn([$couponId]);
        $this->updateInfoMock->expects($this->exactly(2))->method('getCouponCode')->willReturn($couponCode);
        $this->updateInfoMock->expects($this->exactly(3))->method('isIncrement')->willReturn($isIncrement);

        $this->couponMock->expects($this->once())->method('load')->with($couponCode, 'code')
            ->willReturnSelf();
        $this->couponMock->expects($this->exactly(2))->method('getId')->willReturn($couponId);
        $this->couponMock->expects($this->atLeastOnce())->method('getTimesUsed')->willReturn($timesUsed);
        $this->couponMock->expects($this->any())->method('setTimesUsed')->with($setTimesUsed)->willReturnSelf();
        $this->couponMock->expects($this->any())->method('save')->willReturnSelf();

        $this->updateInfoMock->expects($this->exactly(3))->method('getCustomerId')->willReturn($customerId);

        $this->couponUsageMock->expects($this->once())
            ->method('updateCustomerCouponTimesUsed')
            ->with($customerId, $couponId, $isIncrement)
            ->willReturnSelf();

        $customerRuleMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByCustomerRule', 'getId', 'hasData', 'save'])
            ->addMethods(['getTimesUsed', 'setTimesUsed', 'setCustomerId', 'setRuleId'])
            ->getMock();
        $customerRuleMock->expects($this->once())->method('loadByCustomerRule')->with($customerId, $ruleId)
            ->willReturnSelf();
        $customerRuleMock->expects($this->once())->method('getId')->willReturn($ruleCustomerId);
        $customerRuleMock->expects($this->any())->method('getTimesUsed')->willReturn($timesUsed);
        $customerRuleMock->expects($this->any())->method('setTimesUsed')->willReturn($setTimesUsed);
        $customerRuleMock->expects($this->any())->method('setCustomerId')->willReturn($customerId);
        $customerRuleMock->expects($this->any())->method('setRuleId')->willReturn($ruleId);
        $customerRuleMock->expects($this->once())->method('hasData')->willReturn(true);
        $customerRuleMock->expects($this->once())->method('save')->willReturnSelf();
        $this->ruleCustomerFactoryMock->expects($this->once())->method('create')->willReturn($customerRuleMock);

        $ruleMock = $this->getMockBuilder(Rule::class)
            ->onlyMethods(['load', 'getId', 'loadCouponCode', 'save'])
            ->addMethods(['getTimesUsed', 'setTimesUsed'])
            ->disableOriginalConstructor()
            ->getMock();
        $ruleMock->expects($this->once())->method('load')->willReturnSelf();
        $ruleMock->expects($this->once())->method('getId')->willReturn(true);
        $ruleMock->expects($this->once())->method('loadCouponCode')->willReturnSelf();
        $ruleMock->expects($this->any())->method('getTimesUsed')->willReturn($timesUsed);
        $ruleMock->expects($this->any())->method('setTimesUsed')->willReturn($setTimesUsed);
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($ruleMock);

        $this->processor->process($this->updateInfoMock);
    }

    /**
     * @return array
     */
    public function dataProvider(): array
    {
        return [
            [true, 1],
            [true, 0],
            [false, 1],
            [false, 0]
        ];
    }
}
