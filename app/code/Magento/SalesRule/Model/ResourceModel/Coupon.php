<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * SalesRule Resource Coupon
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coupon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb implements
    \Magento\SalesRule\Model\Spi\CouponResourceInterface
{
    /**
     * Constructor adds unique fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('salesrule_coupon', 'coupon_id');
        $this->addUniqueField(['field' => 'code', 'title' => __('Coupon with the same code')]);
    }

    /**
     * Perform actions before object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {
        // maintain single primary coupon per rule
        $object->setIsPrimary($object->getIsPrimary() ? 1 : null);

        return parent::_beforeSave($object);
    }

    /**
     * Load primary coupon (is_primary = 1) for specified rule
     *
     * @param \Magento\SalesRule\Model\Coupon $object
     * @param \Magento\SalesRule\Model\Rule|int $rule
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadPrimaryByRule(\Magento\SalesRule\Model\Coupon $object, $rule)
    {
        $connection = $this->getConnection();

        if ($rule instanceof \Magento\SalesRule\Model\Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'rule_id = :rule_id'
        )->where(
            'is_primary = :is_primary'
        );

        $data = $connection->fetchRow($select, [':rule_id' => $ruleId, ':is_primary' => 1]);

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);
        return true;
    }

    /**
     * Check if code exists
     *
     * @param string $code
     * @return bool
     */
    public function exists($code)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable(), 'code');
        $select->where('code = :code');

        if ($connection->fetchOne($select, ['code' => $code]) === false) {
            return false;
        }
        return true;
    }

    /**
     * Update auto generated Specific Coupon if its rule changed
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return $this
     */
    public function updateSpecificCoupons(\Magento\SalesRule\Model\Rule $rule)
    {
        if (!$rule || !$rule->getId() || !$rule->hasDataChanges()) {
            return $this;
        }

        $updateArray = [];
        if ($rule->dataHasChangedFor('uses_per_coupon')) {
            $updateArray['usage_limit'] = $rule->getUsesPerCoupon();
        }

        if ($rule->dataHasChangedFor('uses_per_customer')) {
            $updateArray['usage_per_customer'] = $rule->getUsesPerCustomer();
        }

        if (!empty($updateArray)) {
            $this->getConnection()->update(
                $this->getTable('salesrule_coupon'),
                $updateArray,
                ['rule_id = ?' => $rule->getId()]
            );
        }

        return $this;
    }
}
