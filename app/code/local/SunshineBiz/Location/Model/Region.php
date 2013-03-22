<?php

/**
 * SunshineBiz_Location region model
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Region extends Mage_Directory_Model_Region {

    protected $_helper;

    protected function _construct() {
        $this->_init('SunshineBiz_Location_Model_Resource_Region');
        $this->_helper = Mage::helper('SunshineBiz_Location_Helper_Data');
    }

    public function loadByLocale($regionId, $locale = null) {

        if ($locale) {
            $this->_beforeLoad($regionId);
            $this->_getResource()->loadByLocale($this, $regionId, $locale);
            $this->_afterLoad();
            $this->setOrigData();
            $this->_hasDataChanges = false;

            return $this;
        }

        $this->load($regionId);
        return $this;
    }

    public function getLocaleName() {
        return $this->getData('name');
    }

    public function dataChanged($data) {
        
        if ($this->getId() > 0) {
            //对语言环境记录的修改
            if ($this->getLocale()) {
                //拟修改名称为空或等于默认名称，删除该语言环境记录
                //拟修改名称与原名称相同，不对该语言环境记录作修改
                return !array_key_exists('name', $data) || !$data['name'] || $this->getDefaultName() === $data['name'] || $this->getLocaleName() !== $data['name'];
            }
            //增加语言环境记录
            if ($data['locale']) {
                //拟增加记录名称为空或等于默认名称时，不增加该记录
                return array_key_exists('name', $data) && $data['name'] && $this->getDefaultName() !== $data['name'];
            }
            //判断是否需要对非语言环境记录进行修改
            return $this->getCountryId() !== $data['country_id'] || $this->getCode() !== $data['code'] || $this->getDefaultName() !== $data['default_name'];
        } else {
            //新增非语言环境记录
            return true;
        }
    }

    public function validate() {
        
        $errors = array();
        if ($this->getId() > 0 && $this->getLocale() && !$this->getDelete()) {
            if (!$this->_getResource()->isUniqueName($this)) {
                $errors[] = $this->_helper->__('In the same country, the region with the same %s name already exists.', $this->_helper->getLocaleLabel($this->getLocale()));
            }
        }
        
        if (!Zend_Validate::is($this->getDefaultName(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Default name is required field.');
        }
        
        if (!Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Country is required field.');
        }
        
        if (empty($errors)) {
            return true;
        }
        
        return $errors;
    }

    public function save() {

        if ($this->getId() > 0 && $this->getLocale() && $this->getDelete()) {
            $this->deleteLocaleName($this->getLocale());
            return $this;
        }

        parent::save();
        return $this;
    }

    public function deleteLocaleName($locale) {
        $this->_getResource()->beginTransaction();
        try {
            $this->_beforeDelete();
            $this->_getResource()->deleteLocaleName($this, $locale);
            $this->_afterDelete();

            $this->_getResource()->commit();
            $this->_afterDeleteCommit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        return $this;
    }

    public function getAreas() {
        $collection = Mage::getResourceModel('SunshineBiz_Location_Model_Resource_Area_Collection');
        $collection->addRegionFilter($this->getId());
        $collection->load();

        return $collection;
    }

}