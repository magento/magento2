<?php

/**
 * SunshineBiz_Location Region resource
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Resource_Region extends Mage_Directory_Model_Resource_Region {

    protected function _initUniqueFields() {
        $this->_uniqueFields = array(
            array(
                'field' => array('code', 'country_id'),
                'title' => Mage::helper('SunshineBiz_Location_Helper_Data')->__('In the same country, the region with the same code')
            ),
            array(
                'field' => array('default_name', 'country_id'),
                'title' => Mage::helper('SunshineBiz_Location_Helper_Data')->__('In the same country, the region with the same default name')
            ),
        );
        return $this;
    }

    public function isUniqueName($region) {
        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('rname.region_id = region.region_id AND rname.locale = ?', $region->getLocale());
        $select = $adapter->select()
                ->from(array('region' => $this->getMainTable()))
                ->joinLeft(
                        array('rname' => $this->_regionNameTable), $joinCondition)
                ->where('region.country_id = ?', $region->getCountryId())
                ->where('rname.name = ?', $region->getLocaleName())
                ->where('region.region_id <> ?', $region->getId());
        $data = $adapter->fetchRow($select);
        if ($data) {
            return false;
        }

        return true;
    }

    public function loadByLocale($region, $regionId, $locale) {

        $adapter = $this->_getReadAdapter();
        $joinCondition = $adapter->quoteInto('rname.region_id = region.region_id AND rname.locale = ?', $locale);
        $select = $adapter->select()
                ->from(array('region' => $this->getMainTable()))
                ->joinLeft(
                        array('rname' => $this->_regionNameTable), $joinCondition, array('name', 'locale'))
                ->where('region.region_id = ?', $regionId);
        $data = $adapter->fetchRow($select);
        if ($data) {
            $region->setData($data);
        }
        $this->unserializeFields($region);
        $this->_afterLoad($region);

        return $this;
    }

    public function save(Mage_Core_Model_Abstract $region) {
        if ($region->getId() > 0 && $region->getLocale()) {
            $this->saveLocaleName($region);
            return $this;
        }

        parent::save($region);
        return $this;
    }

    protected function saveLocaleName(SunshineBiz_Location_Model_Region $region) {
        $this->_serializeFields($region);
        $this->_beforeSave($region);
        $adapter = $this->_getReadAdapter();
        $condition = 'locale = :locale and region_id = :regionId';
        $bind = array('locale' => $region->getLocale(), 'regionId' => $region->getId());
        $select = $adapter->select()
                ->from($this->_regionNameTable)
                ->where($condition);
        $data = $adapter->fetchRow($select, $bind);
        if ($data) {
            $where = array(
                'locale = ?' => $region->getLocale(),
                'region_id = ?' => $region->getId()
            );
            $this->_getWriteAdapter()->update($this->_regionNameTable, array('name' => $region->getLocaleName()), $where);
        } else {
            $data['locale'] = $region->getLocale();
            $data['region_id'] = $region->getId();
            $data['name'] = $region->getLocaleName();
            $this->_getWriteAdapter()->insert($this->_regionNameTable, $data);
        }

        $this->unserializeFields($region);
        $this->_afterSave($region);

        return $this;
    }

    public function deleteLocaleName(SunshineBiz_Location_Model_Region $region, $locale) {
        $this->_beforeDelete($region);
        $where = array(
            'locale = ?' => $locale,
            'region_id = ?' => $region->getId()
        );
        $this->_getWriteAdapter()->delete($this->_regionNameTable, $where);
        $this->_afterDelete($region);
        return $this;
    }

}