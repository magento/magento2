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

/**
 * Consumer for export coupons generation.
 */
class Consumer
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CouponManagementInterface
     */
    private $couponManager;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Consumer constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param CouponManagementInterface $couponManager
     * @param Filesystem $filesystem
     * @param NotifierInterface $notifier
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CouponManagementInterface $couponManager,
        Filesystem $filesystem,
        NotifierInterface $notifier
    ) {
        $this->logger = $logger;
        $this->couponManager = $couponManager;
        $this->filesystem = $filesystem;
        $this->notifier = $notifier;
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
