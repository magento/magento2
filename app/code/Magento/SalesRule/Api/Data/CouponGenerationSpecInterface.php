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
 * @since 100.0.2
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
     * Get format of generated coupon code
     *
     * @return string
     */
    public function getFormat();

    /**
     * Set format for generated coupon code
     *
     * @param string $format
     * @return $this
     */
    public function setFormat($format);

    /**
     * Number of coupons to generate
     *
     * @return int
     */
    public function getQuantity();

    /**
     * Set number of coupons to generate
     *
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * Get length of coupon code
     *
     * @return int
     */
    public function getLength();

    /**
     * Set length of coupon code
     *
     * @param int $length
     * @return $this
     */
    public function setLength($length);

    /**
     * Get the prefix
     *
     * @return string|null
     */
    public function getPrefix();

    /**
     * Set the prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get the suffix
     *
     * @return string|null
     */
    public function getSuffix();

    /**
     * Set the suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix);

    /**
     * Get the spacing where the delimiter should exist
     *
     * @return int|null
     */
    public function getDelimiterAtEvery();

    /**
     * Set the spacing where the delimiter should exist
     *
     * @param int $delimiterAtEvery
     * @return $this
     */
    public function setDelimiterAtEvery($delimiterAtEvery);

    /**
     * Get the delimiter
     *
     * @return string|null
     */
    public function getDelimiter();

    /**
     * Set the delimiter
     *
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
    );
}
