<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Data;

/**
 * Class CouponGenerationSpec
 *
 * @codeCoverageIgnore
 */
class CouponGenerationSpec extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface
{
    const KEY_RULE_ID = 'rule_id';
    const KEY_FORMAT = 'format';
    const KEY_LENGTH = 'length';
    const KEY_USAGE_PER_COUPON = 'usage_per_coupon';
    const KEY_USAGE_PER_CUSTOMER = 'usage_per_customer';
    const KEY_EXPIRATION_DATE = 'expiration_date';
    const KEY_QUANTITY = 'quantity';
    const KEY_PREFIX = 'prefix';
    const KEY_SUFFIX = 'suffix';
    const KEY_DELIMITER_AT_EVERY = 'dash';
    const KEY_DELIMITER = 'delimiter';

    /**
     * Get the id of the rule associated with the coupon
     *
     * @return int
     */
    public function getRuleId()
    {
        return $this->_get(self::KEY_RULE_ID);
    }

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::KEY_RULE_ID, $ruleId);
    }

    /**
     * Get format of generated coupon code
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_get(self::KEY_FORMAT);
    }

    /**
     * Set format for generated coupon code
     *
     * @param string $format
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setData(self::KEY_FORMAT, $format);
    }

    /**
     * Number of coupons to generate
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->_get(self::KEY_QUANTITY);
    }

    /**
     * Set number of coupons to generate
     *
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        return $this->setData(self::KEY_QUANTITY, $quantity);
    }

    /**
     * Get length of coupon code
     *
     * @return int
     */
    public function getLength()
    {
        return $this->_get(self::KEY_LENGTH);
    }

    /**
     * Set length of coupon code
     *
     * @param int $length
     * @return $this
     */
    public function setLength($length)
    {
        return $this->setData(self::KEY_LENGTH, $length);
    }

    /**
     * Get usage limit per coupon
     *
     * @return int|null
     */
    public function getUsagePerCoupon()
    {
        return $this->_get(self::KEY_USAGE_PER_COUPON);
    }

    /**
     * Set usage limit per coupon
     *
     * @param int $usagePerCoupon
     * @return $this
     */
    public function setUsagePerCoupon($usagePerCoupon)
    {
        return $this->setData(self::KEY_USAGE_PER_COUPON, $usagePerCoupon);
    }

    /**
     * Get usage limit per customer
     *
     * @return int|null
     */
    public function getUsagePerCustomer()
    {
        return $this->_get(self::KEY_USAGE_PER_CUSTOMER);
    }

    /**
     * Set usage limit per customer
     *
     * @param int $usagePerCustomer
     * @return $this
     */
    public function setUsagePerCustomer($usagePerCustomer)
    {
        return $this->setData(self::KEY_USAGE_PER_CUSTOMER, $usagePerCustomer);
    }

    /**
     * Get expiration date
     *
     * @return string|null
     */
    public function getExpirationDate()
    {
        return $this->_get(self::KEY_EXPIRATION_DATE);
    }

    /**
     * Set expiration date
     *
     * @param string $expirationDate
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        return $this->setData(self::KEY_EXPIRATION_DATE, $expirationDate);
    }

    /**
     * Get the prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::KEY_PREFIX);
    }

    /**
     * Set the prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::KEY_PREFIX, $prefix);
    }

    /**
     * Get the suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::KEY_SUFFIX);
    }

    /**
     * Set the suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::KEY_SUFFIX, $suffix);
    }

    /**
     * Get the spacing where the delimiter should exist
     *
     * @return int|null
     */
    public function getDelimiterAtEvery()
    {
        return $this->_get(self::KEY_DELIMITER_AT_EVERY);
    }

    /**
     * Set the spacing where the delimiter should exist
     *
     * @param int $delimiterAtEvery
     * @return $this
     */
    public function setDelimiterAtEvery($delimiterAtEvery)
    {
        return $this->setData(self::KEY_DELIMITER_AT_EVERY, $delimiterAtEvery);
    }

    /**
     * Get the delimiter
     *
     * @return string|null
     */
    public function getDelimiter()
    {
        return $this->_get(self::KEY_DELIMITER);
    }

    /**
     * Set the delimiter
     *
     * @param string $delimiter
     * @return $this
     */
    public function setDelimiter($delimiter)
    {
        return $this->setData(self::KEY_DELIMITER, $delimiter);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\CouponGenerationSpecExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
