<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Quote;

use Magento\Quote\Api\Data\CartInterface;
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
     * @param CouponUsagePublisher $couponUsagePublisher
     * @param UpdateInfoFactory $updateInfoFactory
     */
    public function __construct(
        CouponUsagePublisher $couponUsagePublisher,
        UpdateInfoFactory $updateInfoFactory
    ) {
        $this->couponUsagePublisher = $couponUsagePublisher;
        $this->updateInfoFactory = $updateInfoFactory;
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
        $updateInfo->setAppliedRuleIds(explode(',', $quote->getAppliedRuleIds()));
        $updateInfo->setCouponCode((string)$quote->getCouponCode());
        $updateInfo->setCustomerId((int)$quote->getCustomerId());
        $updateInfo->setIsIncrement($increment);

        $this->couponUsagePublisher->publish($updateInfo);
    }
}
