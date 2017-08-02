<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * CouponGenerationSpecInterface
 *
 * @api
 * @since 2.0.0
 */
interface CouponGenerationSpecInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const COUPON_FORMAT_ALPHANUMERIC = 'alphanum';
    const COUPON_FORMAT_ALPHABETICAL = 'alpha';
    const COUPON_FORMAT_NUMERIC = 'num';

    /**
     * Get the id of the rule associated with the coupon
     *
     * @return int
     * @since 2.0.0
     */
    public function getRuleId();

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     * @since 2.0.0
     */
    public function setRuleId($ruleId);

    /**
     * Get format of generated coupon code
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormat();

    /**
     * Set format for generated coupon code
     *
     * @param string $format
     * @return $this
     * @since 2.0.0
     */
    public function setFormat($format);

    /**
     * Number of coupons to generate
     *
     * @return int
     * @since 2.0.0
     */
    public function getQuantity();

    /**
     * Set number of coupons to generate
     *
     * @param int $quantity
     * @return $this
     * @since 2.0.0
     */
    public function setQuantity($quantity);

    /**
     * Get length of coupon code
     *
     * @return int
     * @since 2.0.0
     */
    public function getLength();

    /**
     * Set length of coupon code
     *
     * @param int $length
     * @return $this
     * @since 2.0.0
     */
    public function setLength($length);

    /**
     * Get the prefix
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPrefix();

    /**
     * Set the prefix
     *
     * @param string $prefix
     * @return $this
     * @since 2.0.0
     */
    public function setPrefix($prefix);

    /**
     * Get the suffix
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSuffix();

    /**
     * Set the suffix
     *
     * @param string $suffix
     * @return $this
     * @since 2.0.0
     */
    public function setSuffix($suffix);

    /**
     * Get the spacing where the delimiter should exist
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getDelimiterAtEvery();

    /**
     * Set the spacing where the delimiter should exist
     *
     * @param int $delimiterAtEvery
     * @return $this
     * @since 2.0.0
     */
    public function setDelimiterAtEvery($delimiterAtEvery);

    /**
     * Get the delimiter
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getDelimiter();

    /**
     * Set the delimiter
     *
     * @param string $delimiter
     * @return $this
     * @since 2.0.0
     */
    public function setDelimiter($delimiter);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
    );
}
