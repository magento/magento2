<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule;

use Magento\Framework\Model\AbstractModel;
use Magento\SalesRule\Model\ResourceModel\Rule\Customer as ResourceRuleCustomer;

/**
 * SalesRule Rule Customer Model
 *
 * @method int getRuleId()
 * @method Customer setRuleId(int $value)
 * @method int getCustomerId()
 * @method Customer setCustomerId(int $value)
 * @method int getTimesUsed()
 * @method Customer setTimesUsed(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceRuleCustomer::class);
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
