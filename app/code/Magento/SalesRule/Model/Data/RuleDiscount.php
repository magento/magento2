<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Data;

use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Api\Data\DiscountInterface;

/**
 * Data Model for Rule Discount
 */
class RuleDiscount extends \Magento\Framework\Api\AbstractExtensibleObject implements DiscountInterface
{
    const KEY_DISCOUNT_DATA = 'discount';
    const KEY_RULE_LABEL = 'rule';
    const KEY_RULE_ID = 'rule_id';

    /**
     * Get Discount Data
     *
     * @return Data
     */
    public function getDiscountData()
    {
        return $this->_get(self::KEY_DISCOUNT_DATA);
    }

    /**
     * Get Rule Label
     *
     * @return mixed|null
     */
    public function getRuleLabel()
    {
        return $this->_get(self::KEY_RULE_LABEL);
    }

    /**
     * Set Discount Data
     *
     * @param Data $discountData
     * @return RuleDiscount
     */
    public function setDiscountData(Data $discountData)
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
     * @return string
     */
    public function getRuleID()
    {
        return $this->_get(self::KEY_RULE_ID);
    }

    /**
     * Set Rule ID
     *
     * @param string $ruleID
     * @return RuleDiscount
     */
    public function setRuleID(string $ruleID)
    {
        return $this->setData(self::KEY_RULE_ID, $ruleID);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return DiscountInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param DiscountInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        DiscountInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
