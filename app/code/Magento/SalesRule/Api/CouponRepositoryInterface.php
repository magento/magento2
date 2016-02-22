<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api;

/**
 * Coupon CRUD interface
 *
 * @api
 */
interface CouponRepositoryInterface
{
    /**
     * Save coupon.
     *
     * @param \Magento\SalesRule\Api\Data\CouponInterface $coupon
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a coupon ID is sent but the coupon does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\SalesRule\Api\Data\CouponInterface $coupon);

    /**
     * Get coupon by coupon id.
     *
     * @param int $couponId
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $couponId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($couponId);

    /**
     * Retrieve coupon.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SalesRule\Api\Data\CouponSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete coupon by coupon id.
     *
     * @param int $couponId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($couponId);
}
