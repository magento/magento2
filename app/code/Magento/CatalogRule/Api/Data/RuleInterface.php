<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Api\Data;

/**
 * @api
 * @since 100.1.0
 */
interface RuleInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'rule_id';

    const NAME = 'name';

    const DESCRIPTION = 'description';

    const IS_ACTIVE = 'is_active';

    const STOP_RULES_PROCESSING = 'stop_rules_processing';

    const SORT_ORDER = 'sort_order';

    const SIMPLE_ACTION = 'simple_action';

    const DISCOUNT_AMOUNT = 'discount_amount';

    /**#@-*/

    /**
     * Returns rule id field
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getRuleId();

    /**
     * @param int $ruleId
     * @return $this
     * @since 100.1.0
     */
    public function setRuleId($ruleId);

    /**
     * Returns rule name
     *
     * @return string
     * @since 100.1.0
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     * @since 100.1.0
     */
    public function setName($name);

    /**
     * Returns rule description
     *
     * @return string|null
     * @since 100.1.0
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     * @since 100.1.0
     */
    public function setDescription($description);

    /**
     * Returns rule activity flag
     *
     * @return int
     * @since 100.1.0
     */
    public function getIsActive();

    /**
     * @param int $isActive
     * @return $this
     * @since 100.1.0
     */
    public function setIsActive($isActive);

    /**
     * Returns rule condition
     *
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface|null
     * @since 100.1.0
     */
    public function getRuleCondition();

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface $condition
     * @return $this
     * @since 100.1.0
     */
    public function setRuleCondition($condition);

    /**
     * Returns stop rule processing flag
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getStopRulesProcessing();

    /**
     * @param int $isStopProcessing
     * @return $this
     * @since 100.1.0
     */
    public function setStopRulesProcessing($isStopProcessing);

    /**
     * Returns rule sort order
     *
     * @return int|null
     * @since 100.1.0
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     * @since 100.1.0
     */
    public function setSortOrder($sortOrder);

    /**
     * Returns rule simple action
     *
     * @return string
     * @since 100.1.0
     */
    public function getSimpleAction();

    /**
     * @param string $action
     * @return $this
     * @since 100.1.0
     */
    public function setSimpleAction($action);

    /**
     * Returns discount amount
     *
     * @return float
     * @since 100.1.0
     */
    public function getDiscountAmount();

    /**
     * @param float $amount
     * @return $this
     * @since 100.1.0
     */
    public function setDiscountAmount($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogRule\Api\Data\RuleExtensionInterface|null
     * @since 100.1.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogRule\Api\Data\RuleExtensionInterface $extensionAttributes
     * @return $this
     * @since 100.1.0
     */
    public function setExtensionAttributes(\Magento\CatalogRule\Api\Data\RuleExtensionInterface $extensionAttributes);
}
