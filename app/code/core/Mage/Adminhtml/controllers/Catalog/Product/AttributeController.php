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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product attribute controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Catalog_Product_AttributeController extends Mage_Adminhtml_Controller_Action
{

    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('Mage_Eav_Model_Entity')->setType(Mage_Catalog_Model_Product::ENTITY)
            ->getTypeId();
    }

    protected function _initAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Attributes'))
             ->_title($this->__('Manage Attributes'));

        if($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $this->loadLayout()
                ->_setActiveMenu('Mage_Catalog::catalog_attributes')
                ->_addBreadcrumb(Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'), Mage::helper('Mage_Catalog_Helper_Data')->__('Catalog'))
                ->_addBreadcrumb(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Product Attributes'),
                    Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Product Attributes'))
            ;
        }
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Attribute'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute')
            ->setEntityTypeId($this->_entityTypeId);
        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('This attribute cannot be edited.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getAttributeData(true);
        if (! empty($data)) {
            $model->addData($data);
        }

        Mage::register('entity_attribute', $model);

        $this->_initAction();

        $this->_title($id ? $model->getName() : $this->__('New Attribute'));

        $item = $id ? Mage::helper('Mage_Catalog_Helper_Data')->__('Edit Product Attribute')
                    : Mage::helper('Mage_Catalog_Helper_Data')->__('New Product Attribute');

        $this->_addBreadcrumb($item, $item);

        $this->getLayout()->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));

        $this->renderLayout();

    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute')
            ->loadByCode($this->_entityTypeId, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        if ($data) {
            /** @var $helperCatalog Mage_Catalog_Helper_Data */
            $helperCatalog = Mage::helper('Mage_Catalog_Helper_Data');
            //labels
            foreach ($data['frontend_label'] as &$value) {
                if ($value) {
                    $value = $helperCatalog->stripTags($value);
                }
            }
        }
        return $data;
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        $groupId = $this->getRequest()->getParam('group');

        if ($data) {
            /** @var $session Mage_Backend_Model_Auth_Session */
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');

            $isNewAttributeSet = false;

            if (isset($data['new_attribute_set_name']) && !empty($data['new_attribute_set_name'])) {
                /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
                $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set');
                $name = Mage::helper('Mage_Adminhtml_Helper_Data')->stripTags($data['new_attribute_set_name']);
                $name = trim($name);
                $attributeSet->setEntityTypeId($this->_entityTypeId)
                    ->load($name, 'attribute_set_name');

                if ($attributeSet->getId()) {
                    $session->addError(
                        Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Set with name \'%s\' already exists.', $name)
                    );
                    $session->setAttributeData($data);
                    $this->_redirect('*/*/edit', array('_current' => true));
                    return;
                }

                try {
                    $attributeSet->setAttributeSetName($name)
                        ->validate();
                    $attributeSet->save();
                    $attributeSet->initFromSkeleton($this->getRequest()->getParam('set'))
                        ->save();

                    /** @var $requestedGroup Mage_Catalog_Model_Product_Attribute_Group */
                    $requestedGroup = Mage::getModel('Mage_Catalog_Model_Product_Attribute_Group')->load($groupId);

                    if (!$requestedGroup->getId()) {
                        throw Mage::exception('Mage_Adminhtml',
                            $this->_helper('Mage_Catalog_Helper_Data')->__('Specified Attribute group id is invalid.')
                        );
                    }

                    foreach ($attributeSet->getGroups() as $group) {
                        if ($group->getAttributeGroupName() == $requestedGroup->getAttributeGroupName()) {
                            $targetGroupId = $group->getAttributeGroupId();
                            break;
                        }
                    }

                    $isNewAttributeSet = true;
                } catch (Mage_Core_Exception $e) {
                    $session->addError($e->getMessage());
                } catch (Exception $e) {
                    $session->addException($e, Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while saving the attribute set.'));
                }
            }

            $redirectBack   = $this->getRequest()->getParam('back', false);
            /* @var $model Mage_Catalog_Model_Entity_Attribute */
            $model = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');
            /* @var $helper Mage_Catalog_Helper_Product */
            $helper = Mage::helper('Mage_Catalog_Helper_Product');

            $id = $this->getRequest()->getParam('attribute_id');

            //validate attribute_code
            if (isset($data['attribute_code'])) {
                $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
                if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                    $session->addError(
                        Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.')
                    );
                    $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }


            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $validatorInput Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
                $validatorInput = Mage::getModel('Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator');
                if (!$validatorInput->isValid($data['frontend_input'])) {
                    foreach ($validatorInput->getMessages() as $message) {
                        $session->addError($message);
                    }
                    $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                    return;
                }
            }

            if ($id) {
                $model->load($id);

                if (!$model->getId()) {
                    $session->addError(
                        Mage::helper('Mage_Catalog_Helper_Data')->__('This Attribute no longer exists'));
                    $this->_redirect('*/*/');
                    return;
                }

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $session->addError(
                        Mage::helper('Mage_Catalog_Helper_Data')->__('This attribute cannot be updated.'));
                    $session->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                * @todo add to helper and specify all relations for properties
                */
                $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
                $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);
            }

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }
            if (!isset($data['is_filterable'])) {
                $data['is_filterable'] = 0;
            }
            if (!isset($data['is_filterable_in_search'])) {
                $data['is_filterable_in_search'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }

            //filter
            $data = $this->_filterPostData($data);
            $model->addData($data);

            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            if ($this->getRequest()->getParam('set') && $groupId) {
                // For creating product attribute on product page we need specify attribute set and group

                $attributeSetId = $isNewAttributeSet ? $attributeSet->getId() : $this->getRequest()->getParam('set');
                $attributeGroupId = $isNewAttributeSet ? $targetGroupId : $groupId;

                $model->setAttributeSetId($attributeSetId);
                $model->setAttributeGroupId($attributeGroupId);
            }

            try {
                $model->save();
                $session->addSuccess(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('The product attribute has been saved.'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                $session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = array(
                        'id'       => $this->getRequest()->getParam('product'),
                        'attribute'=> $model->getId(),
                        '_current' => true
                    );
                    if ($isNewAttributeSet) {
                        $requestParams['new_attribute_set_id'] = $attributeSetId;
                    }
                    $this->_redirect('adminhtml/catalog_product/addAttribute', $requestParams);
                } elseif ($redirectBack) {
                    $this->_redirect('*/*/edit', array('attribute_id' => $model->getId(),'_current'=>true));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $session->setAttributeData($data);
                $this->_redirect('*/*/edit', array('attribute_id' => $id, '_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('Mage_Catalog_Model_Resource_Eav_Attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('This attribute cannot be deleted.'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Catalog_Helper_Data')->__('The product attribute has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
            Mage::helper('Mage_Catalog_Helper_Data')->__('Unable to find an attribute to delete.'));
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Catalog::attributes_attributes');
    }
}
