<?php

/**
 * SunshineBiz_Location area resource
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Resource_Area extends Mage_Core_Model_Resource_Db_Abstract {

    protected $_areaNameTable;

    protected function _beforeSave(Mage_Core_Model_Abstract $area) {
        if ($area->isObjectNew()) {
            $area->setCreatedAt($this->formatDate(true));
        }
        $area->setUpdatedAt($this->formatDate(true));

        return parent::_beforeSave($area);
    }

    protected function _construct() {
        $this->_init('location_area', 'id');
        $this->_areaNameTable = $this->getTable('location_area_name');
    }

    protected function _getLoadSelect($field, $value, $object) {

        $select = parent::_getLoadSelect($field, $value, $object);
        $adapter = $this->_getReadAdapter();
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $areaField = $adapter->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());
        $condition = $adapter->quoteInto('lrn.locale = ?', $locale);
        $select->joinLeft(
                array('lrn' => $this->_areaNameTable), "{$areaField} = lrn.area_id AND {$condition}", array('name'));

        return $select;
    }

    protected function _initUniqueFields() {
        $this->_uniqueFields = array(
            array(
                'field' => array('default_name', 'parent_id'),
                'title' => Mage::helper('SunshineBiz_Location_Helper_Data')->__('In the same parent area, the area with the same default name')
            ),
        );
        return $this;
    }

    public function deleteLocaleName(SunshineBiz_Location_Model_Area $area, $locale) {
        $this->_beforeDelete($area);
        $where = array(
            'area_id = ?' => $area->getId(),
            'locale = ?' => $locale
        );
        $this->_getWriteAdapter()->delete($this->_areaNameTable, $where);
        $this->_afterDelete($area);
        return $this;
    }

    public function isUniqueName($area) {
        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('aname.area_id = area.id AND aname.locale = ?', $area->getLocale());
        $select = $adapter->select()
                ->from(array('area' => $this->getMainTable()))
                ->joinLeft(
                        array('aname' => $this->_areaNameTable), $joinCondition)
                ->where('area.parent_id = ?', $area->getParentId())
                ->where('aname.name = ?', $area->getLocaleName())
                ->where('area.id <> ?', $area->getId());
        $data = $adapter->fetchRow($select);
        if ($data) {
            return false;
        }

        return true;
    }

    public function loadByLocale($area, $areaId, $locale) {

        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('aname.area_id = area.id AND aname.locale = ?', $locale);
        $select = $adapter->select()
                ->from(array('area' => $this->getMainTable()))
                ->joinLeft(
                        array('aname' => $this->_areaNameTable), $joinCondition, array('name', 'locale'))
                ->where('area.id = ?', $areaId);
        $data = $adapter->fetchRow($select);
        if ($data) {
            $area->setData($data);
        }
        $this->unserializeFields($area);
        $this->_afterLoad($area);

        return $this;
    }

    public function save(Mage_Core_Model_Abstract $area) {

        if ($area->getId() > 0 && $area->getLocale()) {
            $this->saveLocaleName($area);
            return $this;
        }

        parent::save($area);
        return $this;
    }

    protected function saveLocaleName(SunshineBiz_Location_Model_Area $area) {
        $this->_serializeFields($area);
        $this->_beforeSave($area);
        $adapter = $this->_getReadAdapter();
        $condition = 'area_id = :areaId and locale = :locale';
        $bind = array('areaId' => $area->getId(), 'locale' => $area->getLocale());
        $select = $adapter->select()->from($this->_areaNameTable)->where($condition);
        $data = $adapter->fetchRow($select, $bind);
        if ($data) {
            $where = array(
                'area_id = ?' => $area->getId(),
                'locale = ?' => $area->getLocale()
            );
            $this->_getWriteAdapter()->update($this->_areaNameTable, array('name' => $area->getLocaleName()), $where);
        } else {
            $data['locale'] = $area->getLocale();
            $data['area_id'] = $area->getId();
            $data['name'] = $area->getLocaleName();
            $this->_getWriteAdapter()->insert($this->_areaNameTable, $data);
        }

        $this->unserializeFields($area);
        $this->_afterSave($area);

        return $this;
    }

}