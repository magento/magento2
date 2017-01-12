<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api;

/**
 * Coupon management interface
 *
 * @api
 */
interface CouponManagementInterface
{
    /**
     * Generate coupon for a rule
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
     * @return string[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec);

    /**
     * Delete coupon by coupon ids.
     *
     * @param int[] $ids
     * @param bool $ignoreInvalidCoupons
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByIds(array $ids, $ignoreInvalidCoupons = true);

    /**
     * Delete coupon by coupon codes.
     *
     * @param string[] $codes
     * @param bool $ignoreInvalidCoupons
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByCodes(array $codes, $ignoreInvalidCoupons = true);
}
