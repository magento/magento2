<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel\Rule;

/**
 * SalesRule Rule Customer Model Resource
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Customer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('salesrule_customer', 'rule_customer_id');
    }

    /**
     * Get rule usage record for a customer
     *
     * @param \Magento\SalesRule\Model\Rule\Customer $rule
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     */
    public function loadByCustomerRule($rule, $customerId, $ruleId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'customer_id = :customer_id'
        )->where(
            'rule_id = :rule_id'
        );
        $data = $connection->fetchRow($select, [':rule_id' => $ruleId, ':customer_id' => $customerId]);
        if (false === $data) {
            // set empty data, as an existing rule object might be used
            $data = [];
        }
        $rule->setData($data);
        return $this;
    }

    /**
     * Delete the time Usage from salesrule_customer table when times_used is 0
     * @param $ruleId
     * @param $customerId
     * @param $updatedTimeUsed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteCustomerTimeUsage($ruleId, $customerId, $updatedTimeUsed)
    {
        $connection = $this->getConnection();
        if ($updatedTimeUsed == 0) {
            $connection->delete(
                $this->getMainTable(),
                ['rule_id = ?' => $ruleId, 'customer_id = ?' => $customerId]
            );
        }
    }
}
