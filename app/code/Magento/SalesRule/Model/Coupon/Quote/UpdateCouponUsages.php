<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Quote;

use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Model\Coupon\Usage\Processor as CouponUsageProcessor;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfo;
use Magento\SalesRule\Model\Coupon\Usage\UpdateInfoFactory;
use Magento\SalesRule\Model\Service\CouponUsagePublisher;

/**
 * Updates the coupon usages from quote
 */
class UpdateCouponUsages
{
    /**
     * @var UpdateInfoFactory
     */
    private $updateInfoFactory;

    /**
     * @var CouponUsagePublisher
     */
    private $couponUsagePublisher;

    /**
     * @var CouponUsageProcessor
     */
    private $processor;

    /**
     * @param CouponUsagePublisher $couponUsagePublisher
     * @param UpdateInfoFactory $updateInfoFactory
     * @param CouponUsageProcessor $processor
     */
    public function __construct(
        CouponUsagePublisher $couponUsagePublisher,
        UpdateInfoFactory $updateInfoFactory,
        CouponUsageProcessor $processor
    ) {
        $this->couponUsagePublisher = $couponUsagePublisher;
        $this->updateInfoFactory = $updateInfoFactory;
        $this->processor = $processor;
    }

    /**
     * Executes the current command
     *
     * @param CartInterface $quote
     * @param bool $increment
     * @return void
     */
    public function execute(CartInterface $quote, bool $increment): void
    {
        if (!$quote->getAppliedRuleIds()) {
            return;
        }

        /** @var UpdateInfo $updateInfo */
        $updateInfo = $this->updateInfoFactory->create();
        $appliedRuleIds = explode(',', $quote->getAppliedRuleIds());
        $appliedRuleIds = array_filter(array_map('intval', array_unique($appliedRuleIds)));
        $updateInfo->setAppliedRuleIds($appliedRuleIds);
        $updateInfo->setCouponCode((string)$quote->getCouponCode());
        $updateInfo->setCustomerId((int)$quote->getCustomerId());
        $updateInfo->setIsIncrement($increment);

        $this->couponUsagePublisher->publish($updateInfo);
        $this->processor->updateCustomerRulesUsages($updateInfo);
    }
}
