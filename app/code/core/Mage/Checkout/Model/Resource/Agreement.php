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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Resource Model for Checkout Agreement
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Resource_Agreement extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('checkout_agreement', 'agreement_id');
    }

    /**
     * Method to run before save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        // format height
        $height = $object->getContentHeight();
        $height = Mage::helper('Mage_Checkout_Helper_Data')->stripTags($height);
        if (!$height) {
            $height = '';
        }
        if ($height && preg_match('/[0-9]$/', $height)) {
            $height .= 'px';
        }
        $object->setContentHeight($height);
        return parent::_beforeSave($object);
    }

    /**
     * Method to run after save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = array('agreement_id = ?' => $object->getId());
        $this->_getWriteAdapter()->delete($this->getTable('checkout_agreement_store'), $condition);

        foreach ((array)$object->getData('stores') as $store) {
            $storeArray = array();
            $storeArray['agreement_id'] = $object->getId();
            $storeArray['store_id'] = $store;
            $this->_getWriteAdapter()->insert($this->getTable('checkout_agreement_store'), $storeArray);
        }

        return parent::_afterSave($object);
    }

    /**
     * Method to run after load
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('checkout_agreement_store'), array('store_id'))
            ->where('agreement_id = :agreement_id');

        if ($stores = $this->_getReadAdapter()->fetchCol($select, array(':agreement_id' => $object->getId()))) {
            $object->setData('store_id', $stores);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get load select
     *
     * @param string $field
     * @param value $value
     * @param Varien_Object $object
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $select->join(
                array('cps' => $this->getTable('checkout_agreement_store')),
                $this->getMainTable() . '.agreement_id = cps.agreement_id'
            )
            ->where('is_active=1')
            ->where('cps.store_id IN (0, ?)', $object->getStoreId())
            ->order('store_id DESC')
            ->limit(1);
        }
        return $select;
    }
}
