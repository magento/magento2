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
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Resource Coupon
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Resource_Coupon extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor adds unique fields
     *
     */
    protected function _construct()
    {
        $this->_init('salesrule_coupon', 'coupon_id');
        $this->addUniqueField(array(
            'field' => 'code',
            'title' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Coupon with the same code')
        ));
    }

    /**
     * Perform actions before object save
     *
     * @param Varien_Object $object
     * @return unknown
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getExpirationDate()) {
            $object->setExpirationDate(new Zend_Db_Expr('NULL'));
        } else if ($object->getExpirationDate() instanceof Zend_Date) {
            $object->setExpirationDate($object->getExpirationDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        // maintain single primary coupon per rule
        $object->setIsPrimary($object->getIsPrimary() ? 1 : null);

        return parent::_beforeSave($object);
    }

    /**
     * Load primary coupon (is_primary = 1) for specified rule
     *
     *
     * @param Mage_SalesRule_Model_Coupon $object
     * @param unknown_type $rule
     * @return unknown
     */
    public function loadPrimaryByRule(Mage_SalesRule_Model_Coupon $object, $rule)
    {
        $read = $this->_getReadAdapter();

        if ($rule instanceof Mage_SalesRule_Model_Rule) {
            $ruleId = $rule->getId();
        } else {
            $ruleId = (int)$rule;
        }

        $select = $read->select()->from($this->getMainTable())
            ->where('rule_id = :rule_id')
            ->where('is_primary = :is_primary');

        $data = $read->fetchRow($select, array(':rule_id' => $ruleId, ':is_primary' => 1));

        if (!$data) {
            return false;
        }

        $object->setData($data);

        $this->_afterLoad($object);
        return true;
    }
}
