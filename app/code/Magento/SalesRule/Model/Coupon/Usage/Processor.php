<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Usage;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Processor to update coupon usage
 */
class Processor
{
    /**
     * @var string
     */
    private const LOCK_NAME = 'coupon_code_';

    /**
     * @var string
     */
    private const ERROR_MESSAGE = "coupon exceeds usage limit.";

    /**
     * @var int
     */
    private const LOCK_TIMEOUT = 60;

    /**
     * @var LockManagerInterface
     */
    private LockManagerInterface $lockManager;

    /**
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $ruleCustomerFactory
     * @param Usage $couponUsage
     * @param CouponRepositoryInterface $couponRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param LockManagerInterface|null $lockManager
     */
    public function __construct(
        private readonly RuleFactory $ruleFactory,
        private readonly CustomerFactory $ruleCustomerFactory,
        private readonly Usage $couponUsage,
        private readonly CouponRepositoryInterface $couponRepository,
        private readonly SearchCriteriaBuilder $criteriaBuilder,
        ?LockManagerInterface $lockManager = null
    ) {
        $this->lockManager = $lockManager ?? ObjectManager::getInstance()->get(LockManagerInterface::class);
    }

    /**
     * Update coupon usage
     *
     * @param UpdateInfo $updateInfo
     */
    public function process(UpdateInfo $updateInfo): void
    {
        if (empty($updateInfo->getAppliedRuleIds())) {
            return;
        }

        $this->updateCouponUsages($updateInfo);
        $this->updateRuleUsages($updateInfo);
        $this->updateCustomerRulesUsages($updateInfo);
    }

    /**
     * Update the number of coupon usages
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param UpdateInfo $updateInfo
     * @throws CouldNotSaveException|LocalizedException
     */
    public function updateCouponUsages(UpdateInfo $updateInfo): void
    {
        $coupons = $this->retrieveCoupons($updateInfo);

        if ($updateInfo->isCouponAlreadyApplied()) {
            return;
        }
        $incrementedCouponIds = [];
        foreach ($coupons as $coupon) {
            $this->lockLoadedCoupon($coupon, $updateInfo, $incrementedCouponIds);
            $incrementedCouponIds[] = $coupon->getId();
        }
    }

    /**
     * Lock loaded coupons
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param Coupon $coupon
     * @param UpdateInfo $updateInfo
     * @param array $incrementedCouponIds
     * @return void
     * @throws CouldNotSaveException
     */
    private function lockLoadedCoupon(Coupon $coupon, UpdateInfo $updateInfo, array $incrementedCouponIds): void
    {
        $isIncrement = $updateInfo->isIncrement();
        $lockName = self::LOCK_NAME . $coupon->getCode();
        if ($this->lockManager->lock($lockName, self::LOCK_TIMEOUT)) {
            try {
                $coupon = $this->couponRepository->getById($coupon->getId());

                if ($updateInfo->isIncrement() && $coupon->getUsageLimit() &&
                    $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {

                    if (!empty($incrementedCouponIds)) {
                        $this->revertCouponTimesUsed($incrementedCouponIds);
                    }
                    throw new CouldNotSaveException(__(sprintf('%s %s', $coupon->getCode(), self::ERROR_MESSAGE)));
                }

                if ($updateInfo->isIncrement() || $coupon->getTimesUsed() > 0) {
                    $coupon->setTimesUsed($coupon->getTimesUsed() + ($isIncrement ? 1 : -1));
                    $coupon->save();
                }
            } finally {
                $this->lockManager->unlock($lockName);
            }
        }
    }

    /**
     * Revert times_used of coupon if exception occurred for multiple applied coupon.
     *
     * @param array $incrementedCouponIds
     * @return void
     * @throws CouldNotSaveException|Exception
     */
    private function revertCouponTimesUsed(array $incrementedCouponIds): void
    {
        foreach ($incrementedCouponIds as $couponId) {
            $coupon = $this->couponRepository->getById($couponId);
            $coupon->setTimesUsed($coupon->getTimesUsed() - 1);
            try {
                $this->couponRepository->save($coupon);
            } catch (Exception $e) {
                throw new CouldNotSaveException(__('Error occurred when saving coupon: %1', $e->getMessage()));
            }
        }
    }

    /**
     * Update the number of rule usages
     *
     * @param UpdateInfo $updateInfo
     */
    public function updateRuleUsages(UpdateInfo $updateInfo): void
    {
        $isIncrement = $updateInfo->isIncrement();
        foreach ($updateInfo->getAppliedRuleIds() as $ruleId) {
            $rule = $this->ruleFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                continue;
            }

            $rule->loadCouponCode();
            if ((!$updateInfo->isCouponAlreadyApplied() && $isIncrement) || !$isIncrement) {
                $rule->setTimesUsed($rule->getTimesUsed() + ($isIncrement ? 1 : -1));
                $rule->save();
            }
        }
    }

    /**
     * Update the number of rules usages per customer
     *
     * @param UpdateInfo $updateInfo
     */
    public function updateCustomerRulesUsages(UpdateInfo $updateInfo): void
    {
        $customerId = $updateInfo->getCustomerId();
        if (!$customerId) {
            return;
        }

        $isIncrement = $updateInfo->isIncrement();
        foreach ($updateInfo->getAppliedRuleIds() as $ruleId) {
            $rule = $this->ruleFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                continue;
            }
            $this->updateCustomerRuleUsages($isIncrement, $ruleId, $customerId);
        }

        $coupons = $this->retrieveCoupons($updateInfo);
        foreach ($coupons as $coupon) {
            $this->couponUsage->updateCustomerCouponTimesUsed($customerId, $coupon->getId(), $isIncrement);
        }
    }

    /**
     * Update the number of rule usages per customer
     *
     * @param bool $isIncrement
     * @param int $ruleId
     * @param int $customerId
     * @throws Exception
     */
    private function updateCustomerRuleUsages(bool $isIncrement, int $ruleId, int $customerId): void
    {
        $ruleCustomer = $this->ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
        if ($ruleCustomer->getId()) {
            if ($isIncrement || $ruleCustomer->getTimesUsed() > 0) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + ($isIncrement ? 1 : -1));
            }
        } elseif ($isIncrement) {
            $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
        }

        if ($ruleCustomer->hasData()) {
            $ruleCustomer->save();
        }
    }

    /**
     * Retrieve coupon from update info
     *
     * @param UpdateInfo $updateInfo
     * @return Coupon[]
     */
    private function retrieveCoupons(UpdateInfo $updateInfo): array
    {

        if (!$updateInfo->getCouponCode() && empty($updateInfo->getCouponCodes())) {
            return [];
        }

        $coupons = $updateInfo->getCouponCodes();
        if ($updateInfo->getCouponCode() && !in_array($updateInfo->getCouponCode(), $coupons)) {
            array_unshift($coupons, $updateInfo->getCouponCode());
        }

        return $this->couponRepository->getList(
            $this->criteriaBuilder->addFilter(
                'code',
                $coupons,
                'in'
            )->create()
        )->getItems();
    }
}
