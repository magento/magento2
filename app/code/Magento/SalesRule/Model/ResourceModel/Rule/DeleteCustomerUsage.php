<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\ResourceModel\Rule;


class DeleteCustomerUsage
{
    /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Customer */
    protected $_customerRuleDetails;

    /**
     * DeleteCustomerUsage constructor.
     * @param Customer $customerRuleDetails
     */
    public function __construct(
        Customer $customerRuleDetails
    )
    {
        $this->_customerRuleDetails = $customerRuleDetails;
    }

    /**
     * Delete the time Usage from salesrule_customer table when times_used is 0
     * @param int $ruleId
     * @param int $customerId
     * @param int $updatedTimeUsed
     */
    public function execute($ruleId, $customerId, $updatedTimeUsed)
    {
        $connection = $this->_customerRuleDetails->getConnection();
        if ($updatedTimeUsed === 0) {
            $connection->delete(
                $connection->getTableName('salesrule_customer'),
                ['rule_id = ?' => $ruleId, 'customer_id = ?' => $customerId]
            );
        }
    }

}