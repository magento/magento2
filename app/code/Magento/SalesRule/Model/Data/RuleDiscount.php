<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Data;

use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\Framework\Api\ExtensionAttributesInterface;

/**
 * Data Model for Rule Discount
 */
class RuleDiscount extends \Magento\Framework\Api\AbstractExtensibleObject implements RuleDiscountInterface
{
    const KEY_DISCOUNT_DATA = 'discount';
    const KEY_RULE_LABEL = 'rule';
    const KEY_RULE_ID = 'rule_id';

    /**
     * Get Discount Data
     *
     * @return DiscountDataInterface
     */
    public function getDiscountData(): DiscountDataInterface
    {
        return $this->_get(self::KEY_DISCOUNT_DATA);
    }

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel(): ?string
    {
        return $this->_get(self::KEY_RULE_LABEL);
    }

    /**
     * Set Discount Data
     *
     * @param DiscountDataInterface $discountData
     * @return RuleDiscount
     */
    public function setDiscountData(DiscountDataInterface $discountData)
    {
        return $this->setData(self::KEY_DISCOUNT_DATA, $discountData);
    }

    /**
     * Set Rule Label
     *
     * @param string $ruleLabel
     * @return RuleDiscount
     */
    public function setRuleLabel(string $ruleLabel)
    {
        return $this->setData(self::KEY_RULE_LABEL, $ruleLabel);
    }

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID(): ?int
    {
        return $this->_get(self::KEY_RULE_ID);
    }

    /**
     * Set Rule ID
     *
     * @param int $ruleID
     * @return RuleDiscount
     */
    public function setRuleID(int $ruleID)
    {
        return $this->setData(self::KEY_RULE_ID, $ruleID);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        ExtensionAttributesInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
