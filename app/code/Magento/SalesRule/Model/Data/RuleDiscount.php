<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Data;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\SalesRule\Api\Data\RuleDiscountInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Data Model for Rule Discount
 */
class RuleDiscount extends AbstractExtensibleObject implements RuleDiscountInterface
{
    const KEY_DISCOUNT_DATA = 'discount';
    const KEY_RULE_LABEL = 'rule';
    const KEY_RULE_ID = 'rule_id';

    /**
     * Get Discount Data
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function getDiscountData()
    {
        return $this->_get(self::KEY_DISCOUNT_DATA);
    }

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel()
    {
        return $this->_get(self::KEY_RULE_LABEL);
    }

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID()
    {
        return $this->_get(self::KEY_RULE_ID);
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
