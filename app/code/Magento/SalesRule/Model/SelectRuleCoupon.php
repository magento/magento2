<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;

/**
 * Select a coupon that belongs to the rule
 */
class SelectRuleCoupon implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private array $coupons = [];

    /**
     * @param CouponRepositoryInterface $couponRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        private readonly CouponRepositoryInterface $couponRepository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    /**
     * Return a coupon that belongs to the rule or null if no coupons are matching the rule
     *
     * @param Rule $rule
     * @param array $coupons
     * @return string|null
     */
    public function execute(Rule $rule, array $coupons): ?string
    {
        $coupons = array_filter($coupons);

        if (empty($coupons)) {
            return null;
        }

        if ($rule->getCouponType() == Rule::COUPON_TYPE_NO_COUPON) {
            return null;
        }

        if ($rule->getCouponCode() && in_array($rule->getCouponCode(), $coupons)) {
            return $rule->getCouponCode();
        }

        if ($rule->getCode() && in_array($rule->getCode(), $coupons)) {
            return $rule->getCode();
        }

        return array_flip($this->getCouponsToRules($coupons))[$rule->getRuleId()] ?? null;
    }

    /**
     * Get coupons to ruleIds mapping
     *
     * @param array $coupons
     * @return array
     */
    public function getCouponsToRules(array $coupons): array
    {
        $couponsToLoad = array_filter(array_diff(array_values($coupons), array_keys($this->coupons)));
        if (!empty($couponsToLoad)) {
            $couponModels = $this->couponRepository->getList(
                $this->criteriaBuilder->addFilter(
                    'code',
                    $couponsToLoad,
                    'in'
                )->create()
            )->getItems();
            foreach ($couponModels as $couponModel) {
                $this->coupons[$couponModel->getCode()] = $couponModel->getRuleId();
            }
        }
        return $this->coupons;
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->coupons = [];
    }
}
