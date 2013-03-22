<?php

/**
 * SunshineBiz_Location Region controller
 *
 * @category   SunshineBiz
 * @package    SunshineBiz_Location
 * @author     iSunshineTech <isunshinetech@gmail.com>
 * @copyright   Copyright (c) 2013 SunshineBiz.commerce, Inc. (http://www.sunshinebiz.cn)
 */
class SunshineBiz_Location_Adminhtml_RegionController extends Mage_Backend_Controller_ActionAbstract {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('SunshineBiz_Location::system_location_regions')
                ->_addBreadcrumb($this->__('System'), $this->__('System'))
                ->_addBreadcrumb($this->__('Locations'), $this->__('Locations'))
                ->_addBreadcrumb($this->__('Regions'), $this->__('Regions'));

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Regions'));

        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $this->_title($this->__('System'))
                ->_title($this->__('Locations'))
                ->_title($this->__('Regions'));

        $regionId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        $model = Mage::getModel('SunshineBiz_Location_Model_Region');
        if ($regionId) {
            $model->loadByLocale($regionId, $locale);
            if (!$model->getId()) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This region no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $model->setCountryId(Mage::helper('Mage_Core_Helper_Data')->getDefaultCountry());
        }

        if ($locale && !$model->getLocale()) {
            $model->setLocale($locale);
        }

        $this->_title($model->getId() ? $model->getDefaultName() : $this->__('New Region'));
        $data = Mage::getSingleton('Mage_Backend_Model_Session')->getRegionData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('locations_region', $model);
        if (isset($regionId)) {
            $breadcrumb = $this->__('Edit Region');
        } else {
            $breadcrumb = $this->__('New Region');
        }

        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->renderLayout();
    }

    protected function _prepareRegionForSave($regionId, $locale, array $data) {

        /** @var $model SunshineBiz_Location_Model_Region */
        $model = $this->_objectManager->create('SunshineBiz_Location_Model_Region')->loadByLocale($regionId, $locale);
        if ($regionId && $model->isObjectNew()) {
            $this->_getSession()->addError($this->__('This region no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        if ($model->dataChanged($data)) {

            if ($model->getId() > 0) {
                if ($model->getLocale()) {
                    //对语言环境记录的修改
                    
                    //拟修改名称为空或等于默认名称，删除该语言环境记录
                    if (!array_key_exists('name', $data) || !$data['name'] || $model->getDefaultName() === $data['name']) {
                        Mage::log('RegionController will delete [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        //对名称应用默认值，即删除语言环境记录
                        $model->setDelete(true);
                    } else {
                        Mage::log('RegionController will update [id: ' . $model->getId() . ', locale: '. $model->getLocale() . ']');
                        //对名称进行修改
                        $model->setName($data['name']);
                    }
                } elseif ($data['locale']) {
                    //增加语言环境记录
                    //拟增加记录名称为空或等于默认名称时，不增加该记录
                    if(array_key_exists('name', $data) && $data['name'] && $model->getDefaultName() !== $data['name']) {
                        Mage::log('RegionController will insert [id: ' . $model->getId() . ', locale: '. $data['locale'] . ']');
                        $model->setName($data['name']);
                        $model->setLocale($data['locale']);
                    }
                } else {
                    Mage::log('RegionController will update [id: ' . $model->getId() . ']');
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
        $regionId = $this->getRequest()->getParam('region_id');
        $locale = $this->getRequest()->getParam('locale');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('*/*/');
            return null;
        }

        $model = $this->_prepareRegionForSave($regionId, $locale, $data);
        if (is_null($model)) {
            return;
        }
        try {
            $model->save();
            $this->_getSession()->addSuccess($this->__('The region has been saved.'));
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
            $this->_getSession()->setRegionData($data);
            $this->_redirect('*/*/edit', array('id' => $regionId, 'locale' => $locale));
            return;
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        $regionId = $this->getRequest()->getParam('id');
        $locale = $this->getRequest()->getParam('locale');
        if ($regionId) {
            try {
                $model = Mage::getModel('SunshineBiz_Location_Model_Region');
                $model->setId($regionId);
                if ($locale) {
                    $model->deleteLocaleName($locale);
                    Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The %s name has been deleted.', Mage::helper('SunshineBiz_Location_Helper_Data')->getLocaleLabel($locale)));
                    $this->_redirect('*/*/edit', array('id' => $regionId, 'locale' => $locale));
                    return;
                }

                $model->delete();
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The region has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $params = array('id' => $regionId);
                if ($locale) {
                    $params['locale'] = $locale;
                }
                $this->_redirect('*/*/edit', $params);
                return;
            }
        }
        Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Unable to find a region to delete.'));
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        $regionIds = $this->getRequest()->getParam('region');
        if (!is_array($regionIds)) {
            Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Please select at least one item to delete.'));
        } else {
            try {
                foreach ($regionIds as $regionId) {
                    Mage::getModel('SunshineBiz_Location_Model_Region')->setId($regionId)->delete();
                }
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess(
                        $this->__('Total of %d region(s) were successfully deleted.', count($regionIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Sunshinebiz_Location::location_regions');
    }

}