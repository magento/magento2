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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tag resourse model
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Resource_Tag extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and primary index
     *
     */
    protected function _construct()
    {
        $this->_init('tag', 'tag_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Tag_Model_Resource_Tag
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => 'name',
            'title' => Mage::helper('Mage_Tag_Helper_Data')->__('Tag')
        ));
        return $this;
    }

    /**
     * Loading tag by name
     *
     * @param Mage_Tag_Model_Tag $model
     * @param string $name
     * @return array|false
     */
    public function loadByName($model, $name)
    {
        if ( $name ) {
            $read = $this->_getReadAdapter();
            $select = $read->select();
            if (Mage::helper('Mage_Core_Helper_String')->strlen($name) > 255) {
                $name = Mage::helper('Mage_Core_Helper_String')->substr($name, 0, 255);
            }

            $select->from($this->getMainTable())
                ->where('name = :name');
            $data = $read->fetchRow($select, array('name' => $name));

            $model->setData(( is_array($data) ) ? $data : array());
        } else {
            return false;
        }
    }

    /**
     * Before saving actions
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Tag_Model_Resource_Tag
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getId() && $object->getStatus() == $object->getApprovedStatus()) {
            $searchTag = new Varien_Object();
            $this->loadByName($searchTag, $object->getName());
            if ($searchTag->getData($this->getIdFieldName())
                    && $searchTag->getStatus() == $object->getPendingStatus()) {
                $object->setId($searchTag->getData($this->getIdFieldName()));
            }
        }

        if (Mage::helper('Mage_Core_Helper_String')->strlen($object->getName()) > 255) {
            $object->setName(Mage::helper('Mage_Core_Helper_String')->substr($object->getName(), 0, 255));
        }

        return parent::_beforeSave($object);
    }

    /**
     * Saving tag's base popularity
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getStore() || !Mage::app()->getStore()->isAdmin()) {
            return parent::_afterSave($object);
        }

        $tagId = ($object->isObjectNew()) ? $object->getTagId() : $object->getId();

        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->insertOnDuplicate($this->getTable('tag_properties'), array(
            'tag_id'            => $tagId,
            'store_id'          => $object->getStore(),
            'base_popularity'   => (!$object->getBasePopularity()) ? 0 : $object->getBasePopularity()
        ));

        return parent::_afterSave($object);
    }

    /**
     * Decrementing tag products quantity as action for product delete
     *
     * @param array $tagsId
     * @return int The number of affected rows
     */
    public function decrementProducts(array $tagsId)
    {
        $writeAdapter = $this->_getWriteAdapter();
        if (empty($tagsId)) {
            return 0;
        }

        return $writeAdapter->update(
            $this->getTable('tag_summary'),
            array('products' => new Zend_Db_Expr('products - 1')),
            array('tag_id IN (?)' => $tagsId)
        );
    }

    /**
     * Retrieve select object for load object data
     * Redeclare parent method just for adding tag's base popularity if flag exists
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getAddBasePopularity() && $object->hasStoreId()) {
            $select->joinLeft(
                array('properties' => $this->getTable('tag_properties')),
                "properties.tag_id = {$this->getMainTable()}.tag_id AND properties.store_id = {$object->getStoreId()}",
                'base_popularity'
            );
        }
        return $select;
    }

    /**
     * Fetch store ids in which tag visible
     *
     * @param Mage_Tag_Model_Resource_Tag $object
     * @return Mage_Tag_Model_Resource_Tag
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from($this->getTable('tag_summary'), array('store_id'))
            ->where('tag_id = :tag_id');
        $storeIds = $read->fetchCol($select, array('tag_id' => $object->getId()));

        $object->setVisibleInStoreIds($storeIds);

        return $this;
    }
}