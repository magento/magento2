<?php

/**
 * SunshineBiz_Location area controller
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Adminhtml_AreaController extends Mage_Backend_Controller_ActionAbstract {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('SunshineBiz_Location::system_location_areas')
                ->_addBreadcrumb($this->__('System'), $this->__('System'))
                ->_addBreadcrumb($this->__('Locations'), $this->__('Locations'))
                ->_addBreadcrumb($this->__('Areas'), $this->__('Areas'));

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Areas'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Areas'));

        $areaId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        $model = Mage::getModel('SunshineBiz_Location_Model_Area');
        if ($areaId) {
            $model->loadByLocale($areaId, $locale);
            if (!$model->getId()) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This area no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $model->setCountryId(Mage::getModel('SunshineBiz_Location_Model_Region')->load($model->getRegionId())->getCountryId());
        } else {
            $model->setCountryId(Mage::helper('Mage_Core_Helper_Data')->getDefaultCountry());
        }

        if ($locale && !$model->getLocale()) {
            $model->setLocale($locale);
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New Area'));
        $data = Mage::getSingleton('Mage_Backend_Model_Session')->getAreaData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('locations_area', $model);
        if (isset($areaId)) {
            $breadcrumb = $this->__('Edit Area');
        } else {
            $breadcrumb = $this->__('New Area');
        }

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->renderLayout();
    }

    protected function _prepareAreaForSave($areaId, $locale, array $data) {

        /** @var $model SunshineBiz_Location_Model_Area */
        $model = $this->_objectManager->create('SunshineBiz_Location_Model_Area')->loadByLocale($areaId, $locale);
        if ($areaId && $model->isObjectNew()) {
            $this->_getSession()->addError($this->__('This area no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        if ($model->dataChanged($data)) {
            
            if ($model->getId() > 0) {
                if ($model->getLocale()) {
                    //对语言环境记录的修改
                    
                    //拟修改名称为空或等于默认名称，删除该语言环境记录
                    if (!array_key_exists('name', $data) || !$data['name'] || $model->getDefaultName() === $data['name']) {
                        Mage::log('AreaController will delete [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        //对名称应用默认值，即删除语言环境记录
                        $model->setDelete(true);
                    } else {
                        Mage::log('AreaController will update [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        //对名称进行修改
                        $model->setName($data['name']);
                    }
                } elseif ($data['locale']) {
                    //增加语言环境记录
                    //拟增加记录名称为空或等于默认名称时，不增加该记录
                    if(array_key_exists('name', $data) && $data['name'] && $model->getDefaultName() !== $data['name']) {
                        Mage::log('AreaController will insert [id: ' . $model->getId() . ', locale: '. $data['locale'] . ']');
                        $model->setName($data['name']);
                        $model->setLocale($data['locale']);
                    }
                } else {
                    Mage::log('AreaController will update [id: ' . $model->getId() . ']');
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
        $areaId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('*/*/');
            return null;
        }

        $model = $this->_prepareAreaForSave($areaId, $locale, $data);
        if (is_null($model)) {
            return;
        }

        try {
            $model->save();
            $this->_getSession()->addSuccess($this->__('The area has been saved.'));
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
            $this->_getSession()->setAreaData($data);
            $this->_redirect('*/*/edit', array('id' => $areaId, 'locale' => $locale));
            return;
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        $areaId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        if ($areaId) {
            try {
                $model = Mage::getModel('SunshineBiz_Location_Model_Area');
                $model->setId($areaId);
                if ($locale) {
                    $model->deleteLocaleName($locale);
                    Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The %s name has been deleted.', Mage::helper('SunshineBiz_Location_Helper_Data')->getLocaleLabel($locale)));
                    $this->_redirect('*/*/edit', array('id' => $areaId, 'locale' => $locale));
                    return;
                }

                $model->delete();
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The area has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $params = array('id' => $areaId);
                if ($locale) {
                    $params['locale'] = $locale;
                }
                $this->_redirect('*/*/edit', $params);
                return;
            }
        }
        Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Unable to find a area to delete.'));
        $this->_redirect('*/*/');
    }

    public function massStatusAction() {

        $areaIds = $this->getRequest()->getParam('area');
        $isActive = $this->getRequest()->getParam('is_active');
        if (!is_array($areaIds)) {
            Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Please select at least one item to change status.'));
        } else {
            try {
                foreach ($areaIds as $areaId) {

                    $model = Mage::getModel('SunshineBiz_Location_Model_Area')->load($areaId);
                    if ($model->getId() && $model->getIsActive() !== $isActive) {
                        $model->setIsActive($isActive)->save();
                    }
                }
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess(
                        $this->__('Total of %d area(s) were successfully changed status.', count($areaIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Sunshinebiz_Location::location_areas');
    }
}