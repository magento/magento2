<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface RuleInterface
 *
 * @api
 * @since 100.0.2
 */
interface RuleInterface extends ExtensibleDataInterface
{
    const FREE_SHIPPING_NONE = 'NONE';
    const FREE_SHIPPING_MATCHING_ITEMS_ONLY = 'MATCHING_ITEMS_ONLY';
    const FREE_SHIPPING_WITH_MATCHING_ITEMS = 'FREE_WITH_MATCHING_ITEMS';

    const DISCOUNT_ACTION_BY_PERCENT = 'by_percent';
    const DISCOUNT_ACTION_FIXED_AMOUNT = 'by_fixed';
    const DISCOUNT_ACTION_FIXED_AMOUNT_FOR_CART = 'cart_fixed';
    const DISCOUNT_ACTION_BUY_X_GET_Y = 'buy_x_get_y';

    const COUPON_TYPE_NO_COUPON = 'NO_COUPON';
    const COUPON_TYPE_SPECIFIC_COUPON = 'SPECIFIC_COUPON';
    const COUPON_TYPE_AUTO = 'AUTO';

    /**
     * Return rule id
     *
     * @return int|null
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
     * Get rule name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set rule name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get display label
     *
     * @return \Magento\SalesRule\Api\Data\RuleLabelInterface[]|null
     */
    public function getStoreLabels();

    /**
     * Set display label
     *
     * @param \Magento\SalesRule\Api\Data\RuleLabelInterface[]|null $storeLabels
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null);

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get a list of websites the rule applies to
     *
     * @return int[]
     */
    public function getWebsiteIds();

    /**
     * Set the websites the rule applies to
     *
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds(array $websiteIds);

    /**
     * Get ids of customer groups that the rule applies to
     *
     * @return int[]
     */
    public function getCustomerGroupIds();

    /**
     * Set the customer groups that the rule applies to
     *
     * @param int[] $customerGroupIds
     * @return $this
     */
    public function setCustomerGroupIds(array $customerGroupIds);

    /**
     * Get the start date when the coupon is active
     *
     * @return string|null
     */
    public function getFromDate();

    /**
     * Set the star date when the coupon is active
     *
     * @param string $fromDate
     * @return $this
     */
    public function setFromDate($fromDate);

    /**
     * Get the end date when the coupon is active
     *
     * @return string|null
     */
    public function getToDate();

    /**
     * Set the end date when the coupon is active
     *
     * @param string $fromDate
     * @return $this
     */
    public function setToDate($fromDate);

    /**
     * Get number of uses per customer
     *
     * @return int
     */
    public function getUsesPerCustomer();

    /**
     * Get number of uses per customer
     *
     * @param int $usesPerCustomer
     * @return $this
     */
    public function setUsesPerCustomer($usesPerCustomer);

    /**
     * Whether the coupon is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive();

    /**
     * Set whether the coupon is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Get condition for the rule
     *
     * @return \Magento\SalesRule\Api\Data\ConditionInterface|null
     */
    public function getCondition();

    /**
     * Set condition for the rule
     *
     * @param \Magento\SalesRule\Api\Data\ConditionInterface|null $condition
     * @return $this
     */
    public function setCondition(ConditionInterface $condition = null);

    /**
     * Get action condition
     *
     * @return \Magento\SalesRule\Api\Data\ConditionInterface|null
     */
    public function getActionCondition();

    /**
     * Set action condition
     *
     * @param \Magento\SalesRule\Api\Data\ConditionInterface|null $actionCondition
     * @return $this
     */
    public function setActionCondition(ConditionInterface $actionCondition = null);

    /**
     * Whether to stop rule processing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStopRulesProcessing();

    /**
     * Set whether to stop rule processing
     *
     * @param bool $stopRulesProcessing
     * @return $this
     */
    public function setStopRulesProcessing($stopRulesProcessing);

    /**
     * TODO: is this field needed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAdvanced();

    /**
     * Set if rule is advanced
     *
     * @param bool $isAdvanced
     * @return $this
     */
    public function setIsAdvanced($isAdvanced);

    /**
     * Return product ids
     *
     * @return int[]|null
     */
    public function getProductIds();

    /**
     * Set product ids
     *
     * @param int[]|null $productIds
     * @return $this
     */
    public function setProductIds(array $productIds = null);

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get simple action of the rule
     *
     * @return string|null
     */
    public function getSimpleAction();

    /**
     * Set simple action
     *
     * @param string $simpleAction
     * @return $this
     */
    public function setSimpleAction($simpleAction);

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount();

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Return maximum qty discount is applied
     *
     * @return float|null
     */
    public function getDiscountQty();

    /**
     * Set maximum qty discount is applied
     *
     * @param float $discountQty
     * @return $this
     */
    public function setDiscountQty($discountQty);

    /**
     * Get discount step
     *
     * @return int
     */
    public function getDiscountStep();

    /**
     * Set discount step
     *
     * @param int $discountStep
     * @return $this
     */
    public function setDiscountStep($discountStep);

    /**
     * Whether the rule applies to shipping
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getApplyToShipping();

    /**
     * Set whether the rule applies to shipping
     *
     * @param bool $applyToShipping
     * @return $this
     */
    public function setApplyToShipping($applyToShipping);

    /**
     * Return how many times the rule has been used
     *
     * @return int
     */
    public function getTimesUsed();

    /**
     * Set how many times the rule has been used
     *
     * @param int $timesUsed
     * @return $this
     */
    public function setTimesUsed($timesUsed);

    /**
     * Return whether the rule is in RSS
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRss();

    /**
     * Set whether the rule is shown in RSS
     *
     * @param bool $isRss
     * @return $this
     */
    public function setIsRss($isRss);

    /**
     * Get coupon type
     *
     * @return string
     */
    public function getCouponType();

    /**
     * Set coupon type
     *
     * @param string $couponType
     * @return $this
     */
    public function setCouponType($couponType);

    /**
     * Whether to auto generate coupon
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseAutoGeneration();

    /**
     * Set whether the rule uses auto coupon generation
     *
     * @param bool $useAutoGeneration
     * @return $this
     */
    public function setUseAutoGeneration($useAutoGeneration);

    /**
     * Return limit of uses per coupon
     *
     * @return int
     */
    public function getUsesPerCoupon();

    /**
     * Set the limit of uses per coupon
     *
     * @param int $usesPerCoupon
     * @return $this
     */
    public function setUsesPerCoupon($usesPerCoupon);

    /**
     * When to grant free shipping
     *
     * @return string|null
     */
    public function getSimpleFreeShipping();

    /**
     * Set when to grant free shipping
     *
     * @param string $simpleFreeShipping
     * @return $this
     */
    public function setSimpleFreeShipping($simpleFreeShipping);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SalesRule\Api\Data\RuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SalesRule\Api\Data\RuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(RuleExtensionInterface $extensionAttributes);
}
