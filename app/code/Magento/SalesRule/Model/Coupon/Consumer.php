<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\SalesRule\Api\CouponManagementInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterface;
use Magento\Framework\Notification\NotifierInterface;
use Psr\Log\LoggerInterface;

/**
 * Consumer for export coupons generation.
 */
class Consumer
{
    /**
     * Consumer constructor.
     * @param LoggerInterface $logger
     * @param CouponManagementInterface $couponManager
     * @param Filesystem $filesystem
     * @param NotifierInterface $notifier
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CouponManagementInterface $couponManager,
        private readonly Filesystem $filesystem,
        private readonly NotifierInterface $notifier
    ) {
    }

    /**
     * Consumer logic.
     *
     * @param CouponGenerationSpecInterface $exportInfo
     * @return void
     */
    public function process(CouponGenerationSpecInterface $exportInfo)
    {
        try {
            $this->couponManager->generate($exportInfo);

            $this->notifier->addMajor(
                __('Your coupons are ready'),
                __('You can check your coupons at sales rule page')
            );
        } catch (LocalizedException $exception) {
            $this->notifier->addCritical(
                __('Error during coupons generator process occurred'),
                __('Error during coupons generator process occurred. Please check logs for detail')
            );
            $this->logger->critical(
                'Something went wrong while coupons generator process. ' . $exception->getMessage()
            );
        }
    }
}
