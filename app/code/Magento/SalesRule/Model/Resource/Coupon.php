<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Resource;

use Magento\Framework\Model\AbstractModel;

/**
 * SalesRule Resource Coupon
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Coupon extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
        if (!$object->getExpirationDate()) {
            $object->setExpirationDate(null);
        } elseif ($object->getExpirationDate() instanceof \Zend_Date) {
            $object->setExpirationDate(
                $object->getExpirationDate()->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)
            );
        }

        // maintain single primary coupon per rule
        $object->setIsPrimary($object->getIsPrimary() ? 1 : null);

        return parent::_beforeSave($object);
    }

    /**
     * Load primary coupon (is_primary = 1) for specified rule
     *
     *
     * @param \Magento\SalesRule\Model\Coupon $object
     * @param \Magento\SalesRule\Model\Rule|int $rule
     * @return bool
     */
    public function loadPrimaryByRule(\Magento\SalesRule\Model\Coupon $object, $rule)
    {
        $read = $this->_getReadAdapter();

        if ($rule instanceof \Magento\SalesRule\Model\Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $select = $read->select()->from(
            $this->getMainTable()
        )->where(
            'rule_id = :rule_id'
        )->where(
            'is_primary = :is_primary'
        );

        $data = $read->fetchRow($select, [':rule_id' => $ruleId, ':is_primary' => 1]);

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
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from($this->getMainTable(), 'code');
        $select->where('code = :code');

        if ($read->fetchOne($select, ['code' => $code]) === false) {
            return false;
        }
        return true;
    }

    /**
     * Update auto generated Specific Coupon if it's rule changed
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

        $ruleNewDate = new \Magento\Framework\Stdlib\DateTime\Date($rule->getToDate());
        $ruleOldDate = new \Magento\Framework\Stdlib\DateTime\Date($rule->getOrigData('to_date'));

        if ($ruleNewDate->compare($ruleOldDate)) {
            $updateArray['expiration_date'] = $rule->getToDate();
        }

        if (!empty($updateArray)) {
            $this->_getWriteAdapter()->update(
                $this->getTable('salesrule_coupon'),
                $updateArray,
                ['rule_id = ?' => $rule->getId()]
            );
        }

        return $this;
    }
}
