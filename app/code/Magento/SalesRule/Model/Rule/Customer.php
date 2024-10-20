<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule;

/**
 * SalesRule Rule Customer Model
 *
 * @method int getRuleId()
 * @method \Magento\SalesRule\Model\Rule\Customer setRuleId(int $value)
 * @method int getCustomerId()
 * @method \Magento\SalesRule\Model\Rule\Customer setCustomerId(int $value)
 * @method int getTimesUsed()
 * @method \Magento\SalesRule\Model\Rule\Customer setTimesUsed(int $value)
 */
class Customer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\SalesRule\Model\ResourceModel\Rule\Customer::class);
    }

    /**
     * Load by customer rule
     *
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     */
    public function loadByCustomerRule($customerId, $ruleId)
    {
        $this->_getResource()->loadByCustomerRule($this, $customerId, $ruleId);
        return $this;
    }
}
