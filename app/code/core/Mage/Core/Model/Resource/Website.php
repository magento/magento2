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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Website Resource Model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Website extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('core_website', 'website_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Core_Model_Resource_Website
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'code',
            'title' => Mage::helper('Mage_Core_Helper_Data')->__('Website with the same code')
        ));
        return $this;
    }

    /**
     * Validate website code before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Website
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!preg_match('/^[a-z]+[a-z0-9_]*$/', $object->getCode())) {
            Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Website code may only contain letters (a-z), numbers (0-9) or underscore(_), the first character must be a letter'));
        }

        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Website
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->getIsDefault()) {
            $this->_getWriteAdapter()->update($this->getMainTable(), array('is_default' => 0));
            $where = array('website_id = ?' => $object->getId());
            $this->_getWriteAdapter()->update($this->getMainTable(), array('is_default' => 1), $where);
        }
        return parent::_afterSave($object);
    }

    /**
     * Remove core configuration data after delete website
     *
     * @param Mage_Core_Model_Abstract $model
     * @return Mage_Core_Model_Resource_Website
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $model)
    {
        $where = array(
            'scope = ?'    => 'websites',
            'scope_id = ?' => $model->getWebsiteId()
        );

        $this->_getWriteAdapter()->delete($this->getTable('core_config_data'), $where);

        return $this;

    }

    /**
     * Retrieve default stores select object
     * Select fields website_id, store_id
     * 
     * @param boolean $withDefault include/exclude default admin website
     * @return Varien_Db_Select
     */
    public function getDefaultStoresSelect($withDefault = false)
    {
        $ifNull  = $this->_getReadAdapter()
            ->getCheckSql('store_group_table.default_store_id IS NULL', '0', 'store_group_table.default_store_id');
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('website_table' => $this->getTable('core_website')),
                array('website_id'))
            ->joinLeft(
                array('store_group_table' => $this->getTable('core_store_group')),
                'website_table.website_id=store_group_table.website_id'
                    . ' AND website_table.default_group_id = store_group_table.group_id',
                array('store_id' => $ifNull)
            );
        if (!$withDefault) {
            $select->where('website_table.website_id <> ?', 0);
        }
        return $select;
    }
}
