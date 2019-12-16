<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\SalesRule\Api\Data\ConditionInterface;
use Magento\SalesRule\Api\Data\RuleExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleLabelInterface;

/**
 * Class Rule
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @codeCoverageIgnore
 */
class Rule extends AbstractExtensibleObject implements RuleInterface
{
    const KEY_RULE_ID = 'rule_id';
    const KEY_NAME = 'name';
    const KEY_STORE_LABELS = 'store_labels';
    const KEY_DESCRIPTION = 'description';
    const KEY_FROM_DATE = 'from_date';
    const KEY_TO_DATE = 'to_date';
    const KEY_USES_PER_CUSTOMER = 'uses_per_customer';
    const KEY_IS_ACTIVE = 'is_active';
    const KEY_CONDITION = 'condition';
    const KEY_ACTION_CONDITION = 'action_condition';
    const KEY_STOP_RULES_PROCESSING = 'stop_rules_processing';
    const KEY_IS_ADVANCED = 'is_advanced';
    const KEY_WEBSITES = 'website_ids';
    const KEY_PRODUCT_IDS = 'product_ids';
    const KEY_CUSTOMER_GROUPS = 'customer_group_ids';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_SIMPLE_ACTION = 'simple_action';
    const KEY_DISCOUNT_AMOUNT = 'discount_amount';
    const KEY_DISCOUNT_QTY = 'discount_qty';
    const KEY_DISCOUNT_STEP = 'discount_step';
    const KEY_APPLY_TO_SHIPPING = 'apply_to_shipping';
    const KEY_TIMES_USED = 'times_used';
    const KEY_IS_RSS = 'is_rss';
    const KEY_COUPON_TYPE = 'coupon_type';
    const KEY_USE_AUTO_GENERATION = 'use_auto_generation';
    const KEY_USES_PER_COUPON = 'uses_per_coupon';
    const KEY_SIMPLE_FREE_SHIPPING = 'simple_free_shipping';

    /**
     * Return rule id
     *
     * @return int|null
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
     * Get rule name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * Set rule name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::KEY_DESCRIPTION);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::KEY_DESCRIPTION, $description);
    }

    /**
     * Get the start date when the coupon is active
     *
     * @return string|null
     */
    public function getFromDate()
    {
        return $this->_get(self::KEY_FROM_DATE);
    }

    /**
     * Set the star date when the coupon is active
     *
     * @param string $fromDate
     * @return $this
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::KEY_FROM_DATE, $fromDate);
    }

    /**
     * Get the end date when the coupon is active
     *
     * @return string|null
     */
    public function getToDate()
    {
        return $this->_get(self::KEY_TO_DATE);
    }

    /**
     * Set the end date when the coupon is active
     *
     * @param string $toDate
     * @return $this
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::KEY_TO_DATE, $toDate);
    }

    /**
     * Get number of uses per customer
     *
     * @return int
     */
    public function getUsesPerCustomer()
    {
        return $this->_get(self::KEY_USES_PER_CUSTOMER);
    }

    /**
     * Set number of uses per customer
     *
     * @param int $usesPerCustomer
     * @return $this
     */
    public function setUsesPerCustomer($usesPerCustomer)
    {
        return $this->setData(self::KEY_USES_PER_CUSTOMER, $usesPerCustomer);
    }

    /**
     * Whether the rule is active
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsActive()
    {
        return $this->_get(self::KEY_IS_ACTIVE);
    }

    /**
     * Set whether the coupon is active
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * Get condition for the rule
     *
     * @return ConditionInterface|null
     */
    public function getCondition()
    {
        return $this->_get(self::KEY_CONDITION);
    }

    /**
     * Set condition for the rule
     *
     * @param ConditionInterface|null $condition
     * @return $this
     */
    public function setCondition(ConditionInterface $condition = null)
    {
        return $this->setData(self::KEY_CONDITION, $condition);
    }

    /**
     * Get action condition
     *
     * @return ConditionInterface|null
     */
    public function getActionCondition()
    {
        return $this->_get(self::KEY_ACTION_CONDITION);
    }

    /**
     * Set action condition
     *
     * @param ConditionInterface|null $actionCondition
     * @return $this
     */
    public function setActionCondition(ConditionInterface $actionCondition = null)
    {
        return $this->setData(self::KEY_ACTION_CONDITION, $actionCondition);
    }

    /**
     * Whether to stop rule processing
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStopRulesProcessing()
    {
        return $this->_get(self::KEY_STOP_RULES_PROCESSING);
    }

    /**
     * Set whether to stop rule processing
     *
     * @param bool $stopRulesProcessing
     * @return $this
     */
    public function setStopRulesProcessing($stopRulesProcessing)
    {
        return $this->setData(self::KEY_STOP_RULES_PROCESSING, $stopRulesProcessing);
    }

    /**
     * TODO: is this field needed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAdvanced()
    {
        return $this->_get(self::KEY_IS_ADVANCED);
    }

    /**
     * Set Is Advanced
     *
     * @param bool $isAdvanced
     * @return $this
     */
    public function setIsAdvanced($isAdvanced)
    {
        return $this->setData(self::KEY_IS_ADVANCED, $isAdvanced);
    }

    /**
     * Get display label
     *
     * @return RuleLabelInterface[]|null
     */
    public function getStoreLabels()
    {
        return $this->_get(self::KEY_STORE_LABELS);
    }

    /**
     * Set display label
     *
     * @param RuleLabelInterface[]|null $storeLabels
     * @return $this
     */
    public function setStoreLabels(array $storeLabels = null)
    {
        return $this->setData(self::KEY_STORE_LABELS, $storeLabels);
    }

    /**
     * Get a list of websites the rule applies to
     *
     * @return int[]
     */
    public function getWebsiteIds()
    {
        return $this->_get(self::KEY_WEBSITES);
    }

    /**
     * Set the websites the rule applies to
     *
     * @param int[] $websites
     * @return $this
     */
    public function setWebsiteIds(array $websites)
    {
        return $this->setData(self::KEY_WEBSITES, $websites);
    }

    /**
     * Get ids of customer groups that the rule applies to
     *
     * @return int[]
     */
    public function getCustomerGroupIds()
    {
        return $this->_get(self::KEY_CUSTOMER_GROUPS);
    }

    /**
     * Set the customer groups that the rule applies to
     *
     * @param int[] $customerGroups
     * @return $this
     */
    public function setCustomerGroupIds(array $customerGroups)
    {
        return $this->setData(self::KEY_CUSTOMER_GROUPS, $customerGroups);
    }

    /**
     * Return product ids
     *
     * @return int[]|null
     */
    public function getProductIds()
    {
        return $this->_get(self::KEY_PRODUCT_IDS);
    }

    /**
     * Set product ids
     *
     * @param int[]|null $productIds
     * @return $this
     */
    public function setProductIds(array $productIds = null)
    {
        return $this->setData(self::KEY_PRODUCT_IDS, $productIds);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_get(self::KEY_SORT_ORDER);
    }

    /**
     * Set Sort Order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Get simple action of the rule
     *
     * @return string|null
     */
    public function getSimpleAction()
    {
        return $this->_get(self::KEY_SIMPLE_ACTION);
    }

    /**
     * Set simple action
     *
     * @param string $simpleAction
     * @return $this
     */
    public function setSimpleAction($simpleAction)
    {
        return $this->setData(self::KEY_SIMPLE_ACTION, $simpleAction);
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::KEY_DISCOUNT_AMOUNT);
    }

    /**
     * Set discount amount
     *
     * @param float $discountAmount
     * @return $this
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::KEY_DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Return maximum qty discount is applied
     *
     * @return float
     */
    public function getDiscountQty()
    {
        return $this->_get(self::KEY_DISCOUNT_QTY);
    }

    /**
     * Set maximum qty discount is applied
     *
     * @param float $discountQty
     * @return $this
     */
    public function setDiscountQty($discountQty)
    {
        return $this->setData(self::KEY_DISCOUNT_QTY, $discountQty);
    }

    /**
     * Get discount step
     *
     * @return int
     */
    public function getDiscountStep()
    {
        return $this->_get(self::KEY_DISCOUNT_STEP);
    }

    /**
     * Set discount step
     *
     * @param int $discountStep
     * @return $this
     */
    public function setDiscountStep($discountStep)
    {
        return $this->setData(self::KEY_DISCOUNT_STEP, $discountStep);
    }

    /**
     * Whether the rule applies to shipping
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getApplyToShipping()
    {
        return $this->_get(self::KEY_APPLY_TO_SHIPPING);
    }

    /**
     * Set whether the rule applies to shipping
     *
     * @param bool $applyToShipping
     * @return $this
     */
    public function setApplyToShipping($applyToShipping)
    {
        return $this->setData(self::KEY_APPLY_TO_SHIPPING, $applyToShipping);
    }

    /**
     * Return how many times the rule has been used
     *
     * @return int
     */
    public function getTimesUsed()
    {
        return $this->_get(self::KEY_TIMES_USED);
    }

    /**
     * Set how many times the rule has been used
     *
     * @param int $timesUsed
     * @return $this
     */
    public function setTimesUsed($timesUsed)
    {
        return $this->setData(self::KEY_TIMES_USED, $timesUsed);
    }

    /**
     * Return whether the rule is in RSS
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRss()
    {
        return $this->_get(self::KEY_IS_RSS);
    }

    /**
     * Set whether the rule is shown in RSS
     *
     * @param bool $isRss
     * @return $this
     */
    public function setIsRss($isRss)
    {
        return $this->setData(self::KEY_IS_RSS, $isRss);
    }

    /**
     * Get coupon type
     *
     * @return string
     */
    public function getCouponType()
    {
        return $this->_get(self::KEY_COUPON_TYPE);
    }

    /**
     * Set coupon type
     *
     * @param string $couponType
     * @return $this
     */
    public function setCouponType($couponType)
    {
        return $this->setData(self::KEY_COUPON_TYPE, $couponType);
    }

    /**
     * Whether to auto generate coupon
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseAutoGeneration()
    {
        return $this->_get(self::KEY_USE_AUTO_GENERATION);
    }

    /**
     * Set whether the rule uses auto coupon generation
     *
     * @param bool $useAutoGeneration
     * @return $this
     */
    public function setUseAutoGeneration($useAutoGeneration)
    {
        return $this->setData(self::KEY_USE_AUTO_GENERATION, $useAutoGeneration);
    }

    /**
     * Return limit of uses per coupon
     *
     * @return int
     */
    public function getUsesPerCoupon()
    {
        return $this->_get(self::KEY_USES_PER_COUPON);
    }

    /**
     * Set the limit of uses per coupon
     *
     * @param int $usesPerCoupon
     * @return $this
     */
    public function setUsesPerCoupon($usesPerCoupon)
    {
        return $this->setData(self::KEY_USES_PER_COUPON, $usesPerCoupon);
    }

    /**
     * When to grant free shipping
     *
     * @return string
     */
    public function getSimpleFreeShipping()
    {
        return $this->_get(self::KEY_SIMPLE_FREE_SHIPPING);
    }

    /**
     * Set when to grant free shipping
     *
     * @param string $simpleFreeShipping
     * @return $this
     */
    public function setSimpleFreeShipping($simpleFreeShipping)
    {
        return $this->setData(self::KEY_SIMPLE_FREE_SHIPPING, $simpleFreeShipping);
    }

    /**
     * @inheritdoc
     *
     * @return RuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param RuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        RuleExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
