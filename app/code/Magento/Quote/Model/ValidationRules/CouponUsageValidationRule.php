<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\ValidationRules;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class CouponUsageValidationRule implements QuoteValidationRuleInterface
{
    /**
     * @var string
     */
    private $generalMessage;

    /**
     * @var string
     */
    private $wrongCouponCodeMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CouponRepositoryInterface $couponRepository
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     * @param string $wrongCouponCodeMessage
     */
    public function __construct(
        CouponRepositoryInterface $couponRepository,
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ValidationResultFactory $validationResultFactory,
        string $generalMessage = '',
        string $wrongCouponCodeMessage = ''
    ) {
        $this->couponRepository = $couponRepository;
        $this->logger = $logger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;
        $this->wrongCouponCodeMessage = $wrongCouponCodeMessage;
    }

    /**
     * @inheritdoc
     */
    public function validate(Quote $quote): array
    {
        $validationErrors = [];

        $appliedCouponCode = $quote->getCouponCode();
        if ($appliedCouponCode === '') {
            return [$this->validationResultFactory->create(['errors' => $validationErrors])];
        }

        $couponData = $this->getCouponDataByCode($appliedCouponCode);
        if (!$couponData) {
            $validationErrors = [__($this->wrongCouponCodeMessage)];
            return [$this->validationResultFactory->create(['errors' => $validationErrors])];
        }

        foreach ($couponData as $coupon) {
            $usageLimit = $coupon->getUsageLimit();
            if (!$usageLimit) {
                continue;
            }

            $used = $coupon->getTimesUsed();
            $ordersWithAppliedCoupon = $this->getOrdersWithAppliedCouponCode($appliedCouponCode);
            if ($used >= $usageLimit ||
                $used >= $ordersWithAppliedCoupon ||
                $ordersWithAppliedCoupon >= $usageLimit
            ) {
                $validationErrors = [__($this->generalMessage)];
            }
        }

        return [$this->validationResultFactory->create(['errors' => $validationErrors])];
    }

    /**
     * Get sale rules with a target coupon code
     *
     * @param string $couponCode
     * @return CouponInterface[]|null
     */
    private function getCouponDataByCode(string $couponCode): ?array
    {
        $couponData = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('code', $couponCode)
            ->create();

        try {
            $couponList = $this->couponRepository->getList($searchCriteria);
            if ($couponList->getTotalCount()) {
                $couponData = $couponList->getItems();
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }

        return $couponData;
    }

    /**
     * Get orders qty with applied target coupon code
     *
     * @param string $couponCode
     * @return int
     */
    private function getOrdersWithAppliedCouponCode(string $couponCode): int
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('coupon_code', $couponCode)
            ->create();

        return $this->orderRepository->getList($searchCriteria)->getTotalCount();
    }
}
