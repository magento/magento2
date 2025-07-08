<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;

class GetCoupons implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private array $couponsByCode = [];

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
     * Retrieve coupon data object
     *
     * @param string[] $couponCodes
     * @return CouponInterface[]
     * @throws LocalizedException
     */
    public function execute(array $couponCodes): array
    {
        if (empty($couponCodes)) {
            return [];
        }
        $couponsToLoad = [];
        $coupons = [];
        foreach ($couponCodes as $code) {
            if (!isset($this->couponsByCode[$code])) {
                $couponsToLoad[] = $code;
                continue;
            }
            $coupons[] = $this->couponsByCode[$code];
        }
        if (!empty($couponsToLoad)) {
            $coupons = array_merge($coupons, $this->loadCoupons($couponsToLoad));
        }

        return $coupons;
    }

    /**
     * Load coupons by codes
     *
     * @param array $couponCodes
     * @return CouponInterface[]
     * @throws LocalizedException
     */
    private function loadCoupons(array $couponCodes): array
    {
        $coupons = $this->couponRepository->getList(
            $this->criteriaBuilder->addFilter('code', $couponCodes, 'in')->create()
        )->getItems();
        foreach ($coupons as $coupon) {
            $this->couponsByCode[$coupon->getCode()] = $coupon;
        }
        return $coupons;
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->couponsByCode = [];
    }
}
