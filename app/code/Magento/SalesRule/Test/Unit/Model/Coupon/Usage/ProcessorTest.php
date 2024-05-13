<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Coupon\Usage;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponSearchResultInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Coupon\Usage\Processor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Customer;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var RuleFactory|MockObject
     */
    private $ruleFactoryMock;

    /**
     * @var CustomerFactory|MockObject
     */
    private $ruleCustomerFactoryMock;

    /**
     * @var Usage|MockObject
     */
    private $couponUsageMock;

    /**
     * @var UpdateInfo|MockObject
     */
    private $updateInfoMock;

    /**
     * @var CouponRepositoryInterface|CouponRepositoryInterface&MockObject|MockObject
     */
    private $couponRepository;

    /**
     * @var SearchCriteriaBuilder|SearchCriteriaBuilder&MockObject|MockObject
     */
    private $criteriaBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->createMock(RuleFactory::class);
        $this->ruleCustomerFactoryMock = $this->createMock(CustomerFactory::class);
        $this->couponUsageMock = $this->createMock(Usage::class);
        $this->updateInfoMock = $this->createMock(UpdateInfo::class);
        $this->couponRepository = $this->createMock(CouponRepositoryInterface::class);
        $this->criteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->criteriaBuilder->method('addFilter')->willReturnSelf();
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->criteriaBuilder->method('create')->willReturn($searchCriteria);

        $this->processor = new Processor(
            $this->ruleFactoryMock,
            $this->ruleCustomerFactoryMock,
            $this->couponUsageMock,
            $this->couponRepository,
            $this->criteriaBuilder
        );
    }

    /**
     * Test to update coupon usage
     *
     * @param $isIncrement
     * @param $timesUsed
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

        $this->updateInfoMock->expects($this->atLeastOnce())->method('getAppliedRuleIds')->willReturn([$couponId]);
        $this->updateInfoMock->expects($this->atLeastOnce())->method('getCouponCode')->willReturn($couponCode);
        $this->updateInfoMock->expects($this->atLeastOnce())->method('isIncrement')->willReturn($isIncrement);

        $couponMock = $this->createMock(Coupon::class);
        $searchResult = $this->createMock(CouponSearchResultInterface::class);
        $searchResult->method('getItems')
            ->willReturn([$couponMock]);
        $this->couponRepository->method('getList')->willReturn($searchResult);
        $couponMock->expects($this->atLeastOnce())->method('getId')->willReturn($couponId);
        $couponMock->expects($this->atLeastOnce())->method('getTimesUsed')->willReturn($timesUsed);
        $couponMock->expects($this->any())->method('setTimesUsed')->with($setTimesUsed)->willReturnSelf();
        $couponMock->expects($this->any())->method('save')->willReturnSelf();

        $this->updateInfoMock->expects($this->atLeastOnce())->method('getCustomerId')->willReturn($customerId);

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
        $ruleMock->expects($this->atLeastOnce())->method('load')->willReturnSelf();
        $ruleMock->expects($this->atLeastOnce())->method('getId')->willReturn(true);
        $ruleMock->expects($this->atLeastOnce())->method('loadCouponCode')->willReturnSelf();
        $ruleMock->expects($this->any())->method('getTimesUsed')->willReturn($timesUsed);
        $ruleMock->expects($this->any())->method('setTimesUsed')->willReturn($setTimesUsed);
        $this->ruleFactoryMock->expects($this->atLeastOnce())->method('create')->willReturn($ruleMock);

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
