<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Coupon;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory;
use Magento\SalesRule\Model\Service\CouponUsagePublisher;

/**
 * Update coupon usages
 */
class UpdateCouponUsagesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $couponUsageProcessor
     */
    private $couponUsageProcessor;

    /**
     * @var $updateInfoFactory
     */
    private $updateInfoFactory;

    /**
     * @var $couponUsagePublisher
     */
    private $couponUsagePublisher;

    /**
     * @var $updateCouponUsages
     */
    private $updateCouponUsages;

    /**
     * @var $orderInterface
     */
    private $orderInterface;

    /**
     * @var $updateInfo
     */
    private $updateInfo;

    /**
     * Set up
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->couponUsageProcessor = $this->getMockBuilder(CouponUsageProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->updateInfoFactory = $this->getMockBuilder(UpdateInfoFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->couponUsagePublisher = $this->getMockBuilder(CouponUsagePublisher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInterface = $this->getMockBuilder(OrderInterface::class)
            ->addMethods(['getCoupon','getOrigData'])
            ->getMockForAbstractClass();

        $this->updateInfo = $this->getMockBuilder(UpdateInfo::class)->disableOriginalConstructor()->getMock();

        $couponUsage = new ObjectManager($this);

        $this->updateCouponUsages = $couponUsage->getObject(
            UpdateCouponUsages::class,
            [
                'couponUsageProcessor' => $this->couponUsageProcessor,
                'updateInfoFactory' => $this->updateInfoFactory,
                'couponUsagePublisher' => $this->couponUsagePublisher
            ]
        );
    }

    /**
     * Push data to queue
     *
     * @return void
     */
    public function testExecute()
    {
        $this->orderInterface->expects($this->any())
            ->method('getAppliedRuleIds')
            ->willReturn('1,2');

        $this->orderInterface->expects($this->any())
            ->method('getCouponCode')
            ->willReturn('test');

        $this->orderInterface->expects($this->any())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->updateInfoFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->updateInfo);

        $this->updateInfo->expects($this->any())
            ->method('setAppliedRuleIds')
            ->with(['1','2']);

        $this->updateInfo->expects($this->any())
            ->method('setCouponCode')
            ->with('test');

        $this->updateInfo->expects($this->any())
            ->method('setCustomerId')
            ->with(1);

        $this->updateInfo->expects($this->any())
            ->method('setIsIncrement')
            ->with(true);

        $this->couponUsagePublisher->expects($this->once())
            ->method('publish')
            ->with($this->updateInfo);

        $this->couponUsageProcessor->expects($this->once())
            ->method('updateCustomerRulesUsages')
            ->with($this->updateInfo);

        $this->couponUsageProcessor->expects($this->once())
            ->method('updateCouponUsages')
            ->with($this->updateInfo);

        $this->orderInterface->expects($this->once())
            ->method('getOrigData')
            ->with('coupon_code')
            ->willReturn('test');

        $this->orderInterface->expects($this->once())
            ->method('getStatus')
            ->willReturn('pending');

        $this->updateInfo->expects($this->any())
            ->method('setCouponAlreadyApplied')
            ->with(true);

        $this->updateCouponUsages->execute($this->orderInterface, true);
    }

    /**
     * Test execute function to return subject
     * @return void
     */
    public function testExecuteReturn()
    {
        $this->updateCouponUsages->execute($this->orderInterface, true);
    }
}
