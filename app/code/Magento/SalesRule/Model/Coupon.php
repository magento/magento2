<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

/**
 * SalesRule Coupon Model
 *
 * @api
 * @method \Magento\SalesRule\Model\ResourceModel\Coupon _getResource()
 * @method \Magento\SalesRule\Model\ResourceModel\Coupon getResource()
 * @since 2.0.0
 */
class Coupon extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\SalesRule\Api\Data\CouponInterface
{
    const KEY_COUPON_ID = 'coupon_id';
    const KEY_RULE_ID = 'rule_id';
    const KEY_CODE = 'code';
    const KEY_USAGE_LIMIT = 'usage_limit';
    const KEY_USAGE_PER_CUSTOMER = 'usage_per_customer';
    const KEY_TIMES_USED = 'times_used';
    const KEY_EXPIRATION_DATE = 'expiration_date';
    const KEY_IS_PRIMARY = 'is_primary';
    const KEY_CREATED_AT = 'created_at';
    const KEY_TYPE = 'type';

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\SalesRule\Model\ResourceModel\Coupon::class);
    }

    /**
     * Set rule instance
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return $this
     * @since 2.0.0
     */
    public function setRule(\Magento\SalesRule\Model\Rule $rule)
    {
        $this->setRuleId($rule->getId());
        return $this;
    }

    /**
     * Load primary coupon for specified rule
     *
     * @param \Magento\SalesRule\Model\Rule|int $rule
     * @return $this
     * @since 2.0.0
     */
    public function loadPrimaryByRule($rule)
    {
        $this->getResource()->loadPrimaryByRule($this, $rule);
        return $this;
    }

    /**
     * Load Cart Price Rule by coupon code
     *
     * @param string $couponCode
     * @return $this
     * @since 2.0.0
     */
    public function loadByCode($couponCode)
    {
        $this->load($couponCode, 'code');
        return $this;
    }

    //@codeCoverageIgnoreStart

    /**
     * Get coupon id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCouponId()
    {
        return $this->getData(self::KEY_COUPON_ID);
    }

    /**
     * Set coupon id
     *
     * @param int $couponId
     * @return $this
     * @since 2.0.0
     */
    public function setCouponId($couponId)
    {
        return $this->setData(self::KEY_COUPON_ID, $couponId);
    }

    /**
     * Get the id of the rule associated with the coupon
     *
     * @return int
     * @since 2.0.0
     */
    public function getRuleId()
    {
        return $this->getData(self::KEY_RULE_ID);
    }

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     * @since 2.0.0
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::KEY_RULE_ID, $ruleId);
    }

    /**
     * Get coupon code
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * Set coupon code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Get usage limit
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getUsageLimit()
    {
        return $this->getData(self::KEY_USAGE_LIMIT);
    }

    /**
     * Set usage limit
     *
     * @param int $usageLimit
     * @return $this
     * @since 2.0.0
     */
    public function setUsageLimit($usageLimit)
    {
        return $this->setData(self::KEY_USAGE_LIMIT, $usageLimit);
    }

    /**
     * Get usage limit per customer
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getUsagePerCustomer()
    {
        return $this->getData(self::KEY_USAGE_PER_CUSTOMER);
    }

    /**
     * Set usage limit per customer
     *
     * @param int $usagePerCustomer
     * @return $this
     * @since 2.0.0
     */
    public function setUsagePerCustomer($usagePerCustomer)
    {
        return $this->setData(self::KEY_USAGE_PER_CUSTOMER, $usagePerCustomer);
    }

    /**
     * Get the number of times the coupon has been used
     *
     * @return int
     * @since 2.0.0
     */
    public function getTimesUsed()
    {
        return $this->getData(self::KEY_TIMES_USED);
    }

    /**
     * @param int $timesUsed
     * @return $this
     * @since 2.0.0
     */
    public function setTimesUsed($timesUsed)
    {
        return $this->setData(self::KEY_TIMES_USED, $timesUsed);
    }

    /**
     * Get expiration date
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getExpirationDate()
    {
        return $this->getData(self::KEY_EXPIRATION_DATE);
    }

    /**
     * Set expiration date
     *
     * @param string $expirationDate
     * @return $this
     * @since 2.0.0
     */
    public function setExpirationDate($expirationDate)
    {
        return $this->setData(self::KEY_EXPIRATION_DATE, $expirationDate);
    }

    /**
     * Whether the coupon is primary coupon for the rule that it's associated with
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsPrimary()
    {
        return $this->getData(self::KEY_IS_PRIMARY);
    }

    /**
     * Set whether the coupon is the primary coupon for the rule that it's associated with
     *
     * @param bool $isPrimary
     * @return $this
     * @since 2.0.0
     */
    public function setIsPrimary($isPrimary)
    {
        return $this->setData(self::KEY_IS_PRIMARY, $isPrimary);
    }

    /**
     * Date when the coupon is created
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * Set the date the coupon is created
     *
     * @param string $createdAt
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * Type of coupon
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * @param int $type
     * @return $this
     * @since 2.0.0
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\CouponExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\CouponExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\SalesRule\Api\Data\CouponExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
