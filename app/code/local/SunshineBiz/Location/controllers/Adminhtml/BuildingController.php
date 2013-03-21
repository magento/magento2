<?php

/**
 * SunshineBiz_Location building controller
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Adminhtml_BuildingController extends Mage_Backend_Controller_ActionAbstract {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('SunshineBiz_Location::system_location_buildings')
                ->_addBreadcrumb($this->__('System'), $this->__('System'))
                ->_addBreadcrumb($this->__('Locations'), $this->__('Locations'))
                ->_addBreadcrumb($this->__('Buildings'), $this->__('Buildings'));

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Buildings'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Buildings'));

        $buildingId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        $model = Mage::getModel('SunshineBiz_Location_Model_Building');
        if ($buildingId) {
            $model->loadByLocale($buildingId, $locale);
            if (!$model->getId()) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This building no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $area = Mage::getModel('SunshineBiz_Location_Model_Area')->load($model->getAreaId());
            $model->setRegionId($area->getRegionId());
            $model->setCountryId(Mage::getModel('SunshineBiz_Location_Model_Region')->load($area->getRegionId())->getCountryId());
        } else {
            $model->setCountryId(Mage::helper('Mage_Core_Helper_Data')->getDefaultCountry());
        }

        if ($locale && !$model->getLocale()) {
            $model->setLocale($locale);
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Building'));
        $data = Mage::getSingleton('Mage_Backend_Model_Session')->getBuildingData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('locations_building', $model);
        if (isset($buildingId)) {
            $breadcrumb = $this->__('Edit Building');
        } else {
            $breadcrumb = $this->__('New Building');
        }

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->renderLayout();
    }

    protected function _prepareBuildingForSave($buildingId, $locale, array $data) {

        /** @var $model SunshineBiz_Location_Model_Building */
        $model = $this->_objectManager->create('SunshineBiz_Location_Model_Building')->loadByLocale($buildingId, $locale);
        if ($buildingId && $model->isObjectNew()) {
            $this->_getSession()->addError($this->__('This building no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        if ($model->dataChanged($data)) {
            if ($model->getId() > 0) {
                if ($model->getLocale()) {
                    //对语言环境记录的修改
                    
                    //拟修改名称为空或等于默认名称，删除该语言环境记录
                    if ((!array_key_exists('name', $data) || !$data['name'] || $this->getDefaultName() === $data['name']) &&
                            (!array_key_exists('mnemonic', $data) || !$data['mnemonic'] || $this->getDefaultMnemonic() === $data['mnemonic']) &&
                            (!array_key_exists('address', $data) || !$data['address'] || $this->getDefaultAddress() === $data['address'])) {
                        Mage::log('BuildingController will delete [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        //对名称应用默认值，即删除语言环境记录
                        $model->setDelete(true);
                    } else {
                        Mage::log('BuildingController will update [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        if (!array_key_exists('name', $data) || !$data['name'] || $this->getDefaultName() === $data['name']) {
                            $model->unsetData('name');
                        } else {
                            $model->setName($data['name']);
                        }

                        if (!array_key_exists('mnemonic', $data) || !$data['mnemonic'] || $this->getDefaultMnemonic() === $data['mnemonic']) {
                            $model->unsetData('mnemonic');
                        } else {
                            $model->setMnemonic($data['mnemonic']);
                        }

                        if (!array_key_exists('address', $data) || !$data['address'] || $this->getDefaultAddress() === $data['address']) {
                            $model->unsetData('address');
                        } else {
                            $model->setAddress($data['address']);
                        }
                    }
                } elseif ($data['locale']) {
                    Mage::log('BuildingController will insert [id: ' . $model->getId() . ', locale: '. $data['locale'] . ']');
                    //增加语言环境记录
                    if (array_key_exists('name', $data) && $data['name'] && $this->getDefaultName() !== $data['name']) {
                        $model->setName($data['name']);
                    }

                    if (array_key_exists('mnemonic', $data) && $data['mnemonic'] && $this->getDefaultMnemonic() !== $data['mnemonic']) {
                        $model->setMnemonic($data['mnemonic']);
                    }

                    if (array_key_exists('address', $data) && $data['address'] && $this->getDefaultAddress() !== $data['address']) {
                        $model->setAddress($data['address']);
                    }

                    $model->setLocale($data['locale']);
                } else {
                    //修改非语言环境记录
                    $model->setData($data);
                }
            } else {
                //新增非语言环境记录
                $model->setData($data);
            }

            $result = $model->validate();
            if (is_array($result)) {
                Mage::getSingleton('Mage_Backend_Model_Session')->setRegionData($data);
                foreach ($result as $message) {
                    Mage::getSingleton('Mage_Backend_Model_Session')->addError($message);
                }
                $this->_redirect('*/*/edit', array('_current' => true));
                return null;
            }
        }

        return $model;
    }

    public function saveAction() {

        $redirectBack = $this->getRequest()->getParam('back', false);
        $buildingId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('*/*/');
            return null;
        }

        $model = $this->_prepareBuildingForSave($buildingId, $locale, $data);
        if (is_null($model)) {
            return;
        }

        try {
            $model->save();
            $this->_getSession()->addSuccess($this->__('The building has been saved.'));
            $this->_getSession()->setRegionData(false);

            if ($redirectBack) {
                $this->_redirect('*/*/edit', array(
                    'id' => $model->getId(),
                    'locale' => $locale,
                    '_current' => true
                ));
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setBuildingData($data);
            $this->_redirect('*/*/edit', array('id' => $buildingId, 'locale' => $locale));
            return;
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        $buildingId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        if ($buildingId) {
            try {
                $model = Mage::getModel('SunshineBiz_Location_Model_Building');
                $model->setId($buildingId);
                if ($locale) {
                    $model->deleteLocaleName($locale);
                    Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The %s name has been deleted.', Mage::helper('SunshineBiz_Location_Helper_Data')->getLocaleLabel($locale)));
                    $this->_redirect('*/*/edit', array('id' => $buildingId, 'locale' => $locale));
                    return;
                }

                $model->delete();
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The building has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $params = array('id' => $buildingId);
                if ($locale) {
                    $params['locale'] = $locale;
                }
                $this->_redirect('*/*/edit', $params);
                return;
            }
        }
        Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Unable to find a building to delete.'));
        $this->_redirect('*/*/');
    }

    public function massStatusAction() {

        $buildingIds = $this->getRequest()->getParam('building');
        $isActive = $this->getRequest()->getParam('is_active');
        if (!is_array($buildingIds)) {
            Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Please select at least one item to change status.'));
        } else {
            try {
                foreach ($buildingIds as $buildingId) {

                    $model = Mage::getModel('SunshineBiz_Location_Model_Building')->load($buildingId);
                    if ($model->getId() && $model->getIsActive() !== $isActive) {
                        $model->setIsActive($isActive)->save();
                    }
                }
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess(
                        $this->__('Total of %d building(s) were successfully changed status.', count($buildingIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Sunshinebiz_Location::location_buildings');
    }
}