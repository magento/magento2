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
 * SalesRule resource model
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Resource_Rule extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('salesrule', 'rule_id');
    }

    /**
     * On beforeSave
     *
     * @param Mage_Core_Model_Abstract $object
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getFromDate()) {
            $object->setFromDate(Mage::app()->getLocale()->date());
        }
        if ($object->getFromDate() instanceof Zend_Date) {
            $object->setFromDate($object->getFromDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        if (!$object->getToDate()) {
            $object->setToDate(new Zend_Db_Expr('NULL'));
        } else {
            if ($object->getToDate() instanceof Zend_Date) {
                $object->setToDate($object->getToDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }
        }

        if (!$object->getDiscountQty()) {
            $object->setDiscountQty(new Zend_Db_Expr('NULL'));
        }

        parent::_beforeSave($object);
    }

    /**
     * Get customer uses
     *
     * @param unknown_type $rule
     * @param unknown_type $customerId
     * @return unknown
     */
    public function getCustomerUses($rule, $customerId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()->from($this->getTable('rule_customer'), array('cnt'=>'count(*)'))
            ->where('rule_id = :rule_id')
            ->where('customer_id = :customer_id');
        return $read->fetchOne($select, array(':rule_id' => $rule->getRuleId(), ':customer_id' => $customerId));
    }

    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $labels
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $delete = array();
        $table = $this->getTable('salesrule_label');
        $adapter = $this->_getWriteAdapter();

        foreach ($labels as $storeId => $label) {
            if (Mage::helper('Mage_Core_Helper_String')->strlen($label)) {
                $data = array('rule_id' => $ruleId, 'store_id' => $storeId, 'label' => $label);
                $adapter->insertOnDuplicate($table, $data, array('label'));
            } else {
                $delete[] = $storeId;
            }
        }

        if (!empty($delete)) {
            $adapter->delete($table, array(
                'rule_id=?'       => $ruleId,
                'store_id IN (?)' => $delete
            ));
        }
        return $this;
    }

    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('salesrule_label'), array('store_id', 'label'))
            ->where('rule_id = :rule_id');
        return $this->_getReadAdapter()->fetchPairs($select, array(':rule_id' => $ruleId));
    }

    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($ruleId, $storeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('salesrule_label'), 'label')
            ->where('rule_id = :rule_id')
            ->where('store_id IN(0, :store_id)')
            ->order('store_id DESC');
        return $this->_getReadAdapter()->fetchOne($select, array(':rule_id' => $ruleId, ':store_id' => $storeId));
    }

    /**
     * Return codes of all product attributes currently used in promo rules for specified customer group and website
     *
     * @param unknown_type $websiteId
     * @param unknown_type $customerGroupId
     * @return mixed
     */
    public function getActiveAttributes($websiteId, $customerGroupId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('a' => $this->getTable('salesrule_product_attribute')),
                new Zend_Db_Expr('DISTINCT ea.attribute_code'))
            ->joinInner(array('ea' => $this->getTable('eav_attribute')), 'ea.attribute_id = a.attribute_id', array());
        return $read->fetchAll($select);
    }

    /**
     * Save product attributes currently used in conditions and actions of rule
     *
     * @param Mage_SalesRule_Model_Rule $rule
     * @param mixed $attributes
     * return Mage_SalesRule_Model_Resource_Rule
     */
    public function setActualProductAttributes($rule, $attributes)
    {
        $write = $this->_getWriteAdapter();
        $write->delete($this->getTable('salesrule_product_attribute'), array('rule_id=?' => $rule->getId()));

        //Getting attribute IDs for attribute codes
        $attributeIds = array();
        $select = $this->_getReadAdapter()->select()
                ->from(array('a'=>$this->getTable('eav_attribute')), array('a.attribute_id'))
                ->where('a.attribute_code IN (?)', array($attributes));
        $attributesFound = $this->_getReadAdapter()->fetchAll($select);
        if ($attributesFound) {
            foreach ($attributesFound as $attribute) {
                $attributeIds[] = $attribute['attribute_id'];
            }

            $data = array();
            foreach (explode(',', $rule->getCustomerGroupIds()) as $customerGroupId) {
                foreach (explode(',', $rule->getWebsiteIds()) as $websiteId) {
                    foreach ($attributeIds as $attribute) {
                        $data[] = array (
                            'rule_id'           => $rule->getId(),
                            'website_id'        => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'attribute_id'      => $attribute
                        );
                    }
                }
            }
            $write->insertMultiple($this->getTable('salesrule_product_attribute'), $data);
        }
        return $this;
    }
}
