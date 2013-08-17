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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleShopping Admin Item Types Controller
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @name       Mage_GoogleShopping_Adminhtml_Googleshopping_TypesController
 * @author     Magento Core Team <core@magentocommerce.com>
*/
class Mage_GoogleShopping_Adminhtml_Googleshopping_TypesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Dispatches controller_action_postdispatch_adminhtml Event (as not Adminhtml router)
     */
    public function postDispatch()
    {
        parent::postDispatch();
        if ($this->getFlag('', self::FLAG_NO_POST_DISPATCH)) {
            return;
        }
        $this->_eventManager->dispatch('controller_action_postdispatch_adminhtml', array('controller_action' => $this));
    }

    /**
     * Initialize attribute set mapping object
     *
     * @return Mage_GoogleShopping_Adminhtml_Googleshopping_TypesController
     */
    protected function _initItemType()
    {
        $this->_title($this->__('Google Content Attributes'));

        Mage::register('current_item_type', Mage::getModel('Mage_GoogleShopping_Model_Type'));
        $typeId = $this->getRequest()->getParam('id');
        if (!is_null($typeId)) {
            Mage::registry('current_item_type')->load($typeId);
        }
        return $this;
    }

    /**
     * Initialize general settings for action
     *
     * @return  Mage_GoogleShopping_Adminhtml_Googleshopping_ItemsController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_GoogleShopping::catalog_googleshopping_types')
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Catalog'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Catalog'))
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Google Content'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Google Content'));
        return $this;
    }

    /**
     * List of all maps (items)
     */
    public function indexAction()
    {
        $this->_title($this->__('Google Content Attributes'));

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Attribute Maps'), Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Attribute Maps'))
            ->renderLayout();
    }

    /**
     * Grid for AJAX request
     */
    public function gridAction()
    {
        $this->loadLayout('false');
        $this->renderLayout();
    }

    /**
     * Create new attribute set mapping
     */
    public function newAction()
    {
        try {
            $this->_initItemType();

            $this->_title($this->__('New Google Content Attribute Mapping'));

            $this->_initAction()
                ->_addBreadcrumb(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('New attribute set mapping'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('New attribute set mapping'))
                ->_addContent($this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Types_Edit'))
                ->renderLayout();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't create Attribute Set Mapping."));
            $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
        }
    }

    /**
     * Edit attribute set mapping
     */
    public function editAction()
    {
        $this->_initItemType();
        $typeId = Mage::registry('current_item_type')->getTypeId();

        try {
            $result = array();
            if ($typeId) {
                $collection = Mage::getResourceModel('Mage_GoogleShopping_Model_Resource_Attribute_Collection')
                    ->addTypeFilter($typeId)
                    ->load();
                foreach ($collection as $attribute) {
                    $result[] = $attribute->getData();
                }
            }

            $this->_title($this->__('Google Content Attribute Mapping'));
            Mage::register('attributes', $result);

            $breadcrumbLabel = $typeId ? Mage::helper('Mage_GoogleShopping_Helper_Data')->__('Edit attribute set mapping') : Mage::helper('Mage_GoogleShopping_Helper_Data')->__('New attribute set mapping');
            $this->_initAction()
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbLabel)
                ->_addContent($this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Types_Edit'))
                ->renderLayout();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't edit Attribute Set Mapping."));
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Save attribute set mapping
     */
    public function saveAction()
    {
        /** @var $typeModel Mage_GoogleShopping_Model_Type */
        $typeModel = Mage::getModel('Mage_GoogleShopping_Model_Type');
        $id = $this->getRequest()->getParam('type_id');
        if (!is_null($id)) {
            $typeModel->load($id);
        }

        try {
            $typeModel->setCategory($this->getRequest()->getParam('category'));
            if ($typeModel->getId()) {
                $collection = Mage::getResourceModel('Mage_GoogleShopping_Model_Resource_Attribute_Collection')
                    ->addTypeFilter($typeModel->getId())
                    ->load();
                foreach ($collection as $attribute) {
                    $attribute->delete();
                }
            } else {
                $typeModel->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                    ->setTargetCountry($this->getRequest()->getParam('target_country'));
            }
            $typeModel->save();

            $attributes = $this->getRequest()->getParam('attributes');
            $requiredAttributes = Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getRequiredAttributes();
            if (is_array($attributes)) {
                $typeId = $typeModel->getId();
                foreach ($attributes as $attrInfo) {
                    if (isset($attrInfo['delete']) && $attrInfo['delete'] == 1) {
                        continue;
                    }
                    Mage::getModel('Mage_GoogleShopping_Model_Attribute')
                        ->setAttributeId($attrInfo['attribute_id'])
                        ->setGcontentAttribute($attrInfo['gcontent_attribute'])
                        ->setTypeId($typeId)
                        ->save();
                    unset($requiredAttributes[$attrInfo['gcontent_attribute']]);
                }
            }

            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_GoogleShopping_Helper_Data')->__('The attribute mapping has been saved.'));
            if (!empty($requiredAttributes)) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addSuccess(Mage::helper('Mage_GoogleShopping_Helper_Category')->getMessage());
            }
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't save Attribute Set Mapping."));
        }
        $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Delete attribute set mapping
     */
    public function deleteAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('Mage_GoogleShopping_Model_Type');
            $model->load($id);
            if ($model->getTypeId()) {
                $model->delete();
            }
            $this->_getSession()->addSuccess($this->__('Attribute set mapping was deleted'));
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't delete Attribute Set Mapping."));
        }
        $this->_redirect('*/*/index', array('store' => $this->_getStore()->getId()));
    }

    /**
     * Get Google Content attributes list
     */
    public function loadAttributesAction()
    {
        try {
            $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_GoogleShopping_Block_Adminhtml_Types_Edit_Attributes')
                ->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                ->setTargetCountry($this->getRequest()->getParam('target_country'))
                ->setAttributeSetSelected(true)
                ->toHtml()
            );
        } catch (Exception $e) {
            Mage::logException($e);
            // just need to output text with error
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't load attributes."));
        }
    }

    /**
     * Get available attribute sets
     */
    protected function loadAttributeSetsAction()
    {
        try {
            $this->getResponse()->setBody(
                $this->getLayout()->getBlockSingleton('Mage_GoogleShopping_Block_Adminhtml_Types_Edit_Form')
                    ->getAttributeSetsSelectElement($this->getRequest()->getParam('target_country'))
                    ->toHtml()
            );
        } catch (Exception $e) {
            Mage::logException($e);
            // just need to output text with error
            $this->_getSession()->addError(Mage::helper('Mage_GoogleShopping_Helper_Data')->__("We can't load attribute sets."));
        }
    }

    /**
     * Get store object, basing on request
     *
     * @return Mage_Core_Model_Store
     */
    public function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        if ($storeId == 0) {
            return Mage::app()->getDefaultStoreView();
        }
        return Mage::app()->getStore($storeId);
    }

    /**
     * Check access to this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_GoogleShopping::types');
    }
}
