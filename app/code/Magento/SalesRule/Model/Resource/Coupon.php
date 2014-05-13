<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->addUniqueField(array('field' => 'code', 'title' => __('Coupon with the same code')));
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
        } else if ($object->getExpirationDate() instanceof \Zend_Date) {
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

        $data = $read->fetchRow($select, array(':rule_id' => $ruleId, ':is_primary' => 1));

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

        if ($read->fetchOne($select, array('code' => $code)) === false) {
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

        $updateArray = array();
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
                array('rule_id = ?' => $rule->getId())
            );
        }

        return $this;
    }
}
