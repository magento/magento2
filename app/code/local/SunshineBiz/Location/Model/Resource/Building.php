<?php

/**
 * SunshineBiz_Location building resource
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Resource_Building extends Mage_Core_Model_Resource_Db_Abstract {

    protected $_buildingNameTable;

    protected function _beforeSave(Mage_Core_Model_Abstract $building) {
        if ($building->isObjectNew()) {
            $building->setCreatedAt($this->formatDate(true));
        }
        $building->setUpdatedAt($this->formatDate(true));

        return parent::_beforeSave($building);
    }

    protected function _construct() {
        $this->_init('location_building', 'id');
        $this->_buildingNameTable = $this->getTable('location_building_name');
    }

    protected function _getLoadSelect($field, $value, $object) {

        $select = parent::_getLoadSelect($field, $value, $object);
        $adapter = $this->_getReadAdapter();
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $areaField = $adapter->quoteIdentifier($this->getMainTable() . '.' . $this->getIdFieldName());
        $condition = $adapter->quoteInto('lrn.locale = ?', $locale);
        $select->joinLeft(
                array('lrn' => $this->_buildingNameTable), "{$areaField} = lrn.building_id AND {$condition}", array('name', 'mnemonic', 'address'));

        return $select;
    }

    protected function _initUniqueFields() {
        $this->_uniqueFields = array(
            array(
                'field' => array('default_name', 'area_id'),
                'title' => Mage::helper('SunshineBiz_Location_Helper_Data')->__('In the same area, the building with the same default name')
            ),
        );
        return $this;
    }

    public function deleteLocaleName(SunshineBiz_Location_Model_Building $building, $locale) {
        $this->_beforeDelete($building);
        $where = array(
            'building_id = ?' => $building->getId(),
            'locale = ?' => $locale
        );
        $this->_getWriteAdapter()->delete($this->_buildingNameTable, $where);
        $this->_afterDelete($building);
        return $this;
    }

    public function isUniqueName($building) {
        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('bname.building_id = building.id AND bname.locale = ?', $building->getLocale());
        $select = $adapter->select()
                ->from(array('building' => $this->getMainTable()))
                ->joinLeft(
                        array('bname' => $this->_buildingNameTable), $joinCondition)
                ->where('building.area_id = ?', $building->getAreaId())
                ->where('bname.name = ?', $building->getLocaleName())
                ->where('building.id <> ?', $building->getId());
        $data = $adapter->fetchRow($select);
        if ($data) {
            return false;
        }

        return true;
    }

    public function loadByLocale($building, $buildingId, $locale) {

        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('bname.building_id = building.id AND bname.locale = ?', $locale);
        $select = $adapter->select()
                ->from(array('building' => $this->getMainTable()))
                ->joinLeft(
                        array('bname' => $this->_buildingNameTable), $joinCondition, array('name', 'mnemonic', 'address', 'locale'))
                ->where('building.id = ?', $buildingId);
        $data = $adapter->fetchRow($select);
        if ($data) {
            $building->setData($data);
        }
        $this->unserializeFields($building);
        $this->_afterLoad($building);

        return $this;
    }

    public function save(Mage_Core_Model_Abstract $building) {

        if ($building->getId() > 0 && $building->getLocale()) {
            $this->saveLocaleName($building);
            return $this;
        }

        parent::save($building);
        return $this;
    }

    protected function saveLocaleName(SunshineBiz_Location_Model_Building $building) {
        $this->_serializeFields($building);
        $this->_beforeSave($building);
        $adapter = $this->_getReadAdapter();
        $condition = 'building_id = :buildingId and locale = :locale';
        $bind = array('buildingId' => $building->getId(), 'locale' => $building->getLocale());
        $select = $adapter->select()->from($this->_buildingNameTable)->where($condition);
        $data = $adapter->fetchRow($select, $bind);
        if ($data) {
            $where = array(
                'building_id = ?' => $building->getId(),
                'locale = ?' => $building->getLocale()
            );
            $this->_getWriteAdapter()->update($this->_buildingNameTable, $this->_prepareDataForTable($building, $this->_buildingNameTable), $where);
        } else {
            $data = $this->_prepareDataForTable($building, $this->_buildingNameTable);
            $data['building_id'] = $building->getId();
            $this->_getWriteAdapter()->insert($this->_buildingNameTable, $data);
        }

        $this->unserializeFields($building);
        $this->_afterSave($building);

        return $this;
    }

}