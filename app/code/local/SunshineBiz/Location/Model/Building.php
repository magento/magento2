<?php

/**
 * SunshineBiz_Location building model
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Model_Building extends Mage_Core_Model_Abstract {

    protected $_helper;

    protected function _construct() {
        $this->_init('SunshineBiz_Location_Model_Resource_Building');
        $this->_helper = Mage::helper('SunshineBiz_Location_Helper_Data');
    }

    public function dataChanged($data) {
        
        if ($this->getId() > 0) {
            //对语言环境记录的修改
            if ($this->getLocale()) {
                //拟修改名称为空或等于默认名称，删除该语言环境记录
                //拟修改名称与原名称相同，不对该语言环境记录作修改
                return (!array_key_exists('name', $data) || !$data['name'] || $this->getDefaultName() === $data['name'] || $this->getLocaleName() !== $data['name']) ||
                        (!array_key_exists('mnemonic', $data) || !$data['name'] || $this->getDefaultMnemonic() === $data['mnemonic'] || $this->getLocaleMnemonic() !== $data['mnemonic']) ||
                        (!array_key_exists('address', $data) || !$data['address'] || $this->getDefaultAddress() === $data['address'] || $this->getLocaleAddress() !== $data['address']);
            }
            //增加语言环境记录
            if (isset($data['locale'])) {
                //拟增加记录名称为空或等于默认名称时，不增加该记录
                return (array_key_exists('name', $data) && $data['name'] && $this->getDefaultName() !== $data['name']) ||
                        (array_key_exists('mnemonic', $data) && $data['mnemonic'] && $this->getDefaultMnemonic() !== $data['mnemonic']) ||
                        (array_key_exists('address', $data) && $data['address'] && $this->getDefaultAddress() !== $data['address']);
            }

            //判断是否需要对非语言环境记录进行修改
            return $this->getDefaultMnemonic() !== $data['default_mnemonic'] || $this->getAreaId() !== $data['area_id'] || $this->getDefaultAddress() !== $data['default_address'] || $this->getIsActive() !== $data['is_active'] || $this->getDefaultName() !== $data['default_name'];
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

    public function getLocaleMnemonic() {
        return $this->getData('mnemonic');
        ;
    }

    public function getMnemonic() {
        $name = $this->getData('mnemonic');
        if (is_null($name)) {
            $name = $this->getData('default_mnemonic');
        }
        return $name;
    }

    public function getLocaleAddress() {
        return $this->getData('address');
        ;
    }

    public function getAddress() {
        $name = $this->getData('address');
        if (is_null($name)) {
            $name = $this->getData('default_address');
        }
        return $name;
    }

    public function loadByLocale($buildingId, $locale = null) {

        if ($locale) {
            $this->_beforeLoad($buildingId);
            $this->_getResource()->loadByLocale($this, $buildingId, $locale);
            $this->_afterLoad();
            $this->setOrigData();
            $this->_hasDataChanges = false;

            return $this;
        }

        $this->load($buildingId);
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
        $label = $this->_helper->getLocaleLabel($this->getLocale());
        if ($this->getId() > 0 && $this->getLocale() && !$this->getDelete()) {
           if (!$this->_getResource()->isUniqueName($this)) {
               $errors[] = $this->_helper->__('In the same area, the building with the same %s name already exists.', $label);
           }
        }

        if (!Zend_Validate::is($this->getDefaultName(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Default name is required field.');
        }

        if (!Zend_Validate::is($this->getAreaId(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Area is required field.');
        }

        if (!Zend_Validate::is($this->getDefaultAddress(), 'NotEmpty')) {
            $errors[] = $this->_helper->__('Default address is required field.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}