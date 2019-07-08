<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 * @since 100.0.2
 */
interface CouponInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const TYPE_MANUAL = 0;
    const TYPE_GENERATED = 1;

    /**
     * Get coupon id
     *
     * @return int|null
     */
    public function getCouponId();

    /**
     * Set coupon id
     *
     * @param int $couponId
     * @return $this
     */
    public function setCouponId($couponId);

    /**
     * Get the id of the rule associated with the coupon
     *
     * @return int
     */
    public function getRuleId();

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId);

    /**
     * Get coupon code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set coupon code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get usage limit
     *
     * @return int|null
     */
    public function getUsageLimit();

    /**
     * Set usage limit
     *
     * @param int $usageLimit
     * @return $this
     */
    public function setUsageLimit($usageLimit);

    /**
     * Get usage limit per customer
     *
     * @return int|null
     */
    public function getUsagePerCustomer();

    /**
     * Set usage limit per customer
     *
     * @param int $usagePerCustomer
     * @return $this
     */
    public function setUsagePerCustomer($usagePerCustomer);

    /**
     * Get the number of times the coupon has been used
     *
     * @return int
     */
    public function getTimesUsed();

    /**
     * Set time used.
     *
     * @param int $timesUsed
     * @return $this
     */
    public function setTimesUsed($timesUsed);

    /**
     * Get expiration date
     *
     * @return string|null
     * @deprecated Coupon expiration must follow sales rule expiration date.
     */
    public function getExpirationDate();

    /**
     * Set expiration date
     *
     * @param string $expirationDate
     * @return $this
     * @deprecated Coupon expiration must follow sales rule expiration date.
     */
    public function setExpirationDate($expirationDate);

    /**
     * Whether the coupon is primary coupon for the rule that it's associated with
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsPrimary();

    /**
     * Set whether the coupon is the primary coupon for the rule that it's associated with
     *
     * @param bool $isPrimary
     * @return $this
     */
    public function setIsPrimary($isPrimary);

    /**
     * Date when the coupon is created
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set the date the coupon is created
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Type of coupon
     *
     * @return int|null
     */
    public function getType();

    /**
     * Set type of coupon.
     *
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\CouponExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\CouponExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\CouponExtensionInterface $extensionAttributes
    );
}
