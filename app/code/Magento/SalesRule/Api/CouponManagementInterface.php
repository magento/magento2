<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param bool $ignoreInvalidIds
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByIds(array $ids, $ignoreInvalidIds = true);

    /**
     * Delete coupon by coupon codes.
     *
     * @param string[] $codes
     * @param bool $ignoreInvalidCodes
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByCodes(array $codes, $ignoreInvalidCodes = true);
}
