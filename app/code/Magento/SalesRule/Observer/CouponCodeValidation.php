<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use Magento\SalesRule\Model\Spi\CodeLimitManagerInterface;

/**
 * Validate newly provided coupon code before using it while calculating totals.
 */
class CouponCodeValidation implements ObserverInterface
{
    /**
     * @var CodeLimitManagerInterface
     */
    private $codeLimitManager;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon
     */
    private $couponResource;

    /**
     * CouponCodeValidation constructor.
     *
     * @param CodeLimitManagerInterface $codeLimitManager
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon $couponResource
     */
    public function __construct(
        CodeLimitManagerInterface $codeLimitManager,
        \Magento\SalesRule\Model\ResourceModel\Coupon $couponResource
    ) {
        $this->codeLimitManager = $codeLimitManager;
        $this->couponResource = $couponResource;
    }

    /**
     * @inheritDoc
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        $newCode = $quote->getCouponCode();
        if ($newCode) {
            // Only validating the code if it's a new code.
            $oldCode = $this->couponResource->getCouponCodeByQuoteId($quote->getId());
            if (!$oldCode || (string)$oldCode !== (string)$newCode) {
                try {
                    $this->codeLimitManager->checkRequest($newCode);
                } catch (CodeRequestLimitException $exception) {
                    $quote->setCouponCode('');
                    throw $exception;
                }
            }
        }
    }
}
