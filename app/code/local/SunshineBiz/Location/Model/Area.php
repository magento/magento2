<?php

/**
 * SunshineBiz_Location area model
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Area extends Mage_Core_Model_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('SunshineBiz_Location_Model_Resource_Area');
        $this->_helper = Mage::helper('SunshineBiz_Location_Helper_Data');
    }

    public function dataChanged($data) {
        
        if ($this->getId() > 0) {
            //对语言环境记录的修改
            if ($this->getLocale()) {
                ///拟修改名称为空或等于默认名称，删除该语言环境记录
                //拟修改名称与原名称相同，不对该语言环境记录作修改
                return !array_key_exists('name', $data) || !$data['name'] || $this->getDefaultName() === $data['name'] || $this->getLocaleName() !== $data['name'];
            }
            //增加语言环境记录
            if (isset($data['locale'])) {
                //拟增加记录名称为空或等于默认名称时，不增加该记录
                return array_key_exists('name', $data) && $data['name'] && $this->getDefaultName() !== $data['name'];
            }

            //判断是否需要对非语言环境记录进行修改
            return $this->getIsActive() !== $data['is_active'] || $this->getParentId() !== $data['parent_id'] || $this->getRegionId() !== $data['region_id'] || $this->getDefaultName() !== $data['default_name'];
        } else {
            //新增非语言环境记录
            return true;
        }
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

    public function getLocaleName() {
        return $this->getData('name');
        ;
    }

    public function getName() {
        $name = $this->getData('name');
        if (is_null($name)) {
            $name = $this->getData('default_name');
        }
        return $name;
    }

    public function loadByLocale($areaId, $locale = null) {

        if ($locale) {
            $this->_beforeLoad($areaId);
            $this->_getResource()->loadByLocale($this, $areaId, $locale);
            $this->_afterLoad();
            $this->setOrigData();
            $this->_hasDataChanges = false;

            return $this;
        }

        $this->load($areaId);
        return $this;
    }

    public function save() {

        if ($this->getId() > 0 && $this->getLocale() && $this->getDelete()) {
            $this->deleteLocaleName($this->getLocale());
            return $this;
        }

        parent::save();
        return $this;
    }

    public function validate() {

        $errors = array();
        $parent = Mage::getModel('SunshineBiz_Location_Model_Area')->loadByLocale($this->getParentId(), $this->getLocale());
        $label = $this->_helper->getLocaleLabel($this->getLocale());
        if ($this->getId() > 0 && $this->getLocale() && !$this->getDelete()) {
            if (!$this->_getResource()->isUniqueName($this)) {
                $errors[] = $this->_helper->__('In the same parent area, the area with the same %s name already exists.', $label);
            }

            if ($parent && $parent->getLocaleName() === $this->getLocaleName()) {
                $errors[] = $this->_helper->__('This %s name can\' be the same as its parent\'s %s name.', array($label, $label));
            }
        }

        if (!Zend_Validate::is($this->getDefaultName(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Default name is required field.');
        } else if ($parent && $parent->getDefaultName() === $this->getDefaultName()) {
            $errors[] = $this->_helper->__('This default name can\' be the same as its parent\'s default name.');
        }

        if (!Zend_Validate::is($this->getRegionId(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Region is required field.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}