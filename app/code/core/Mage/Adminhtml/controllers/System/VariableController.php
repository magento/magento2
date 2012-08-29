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
 * Custom Variables admin controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_System_VariableController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize Layout and set breadcrumbs
     *
     * @return Mage_Adminhtml_System_VariableController
     */
    protected function _initLayout()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Adminhtml::system_variable')
            ->_addBreadcrumb(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Custom Variables'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Custom Variables'));
        return $this;
    }

    /**
     * Initialize Variable object
     *
     * @return Mage_Core_Model_Variable
     */
    protected function _initVariable()
    {
        $this->_title($this->__('System'))->_title($this->__('Custom Variables'));

        $variableId = $this->getRequest()->getParam('variable_id', null);
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        /* @var $emailVariable Mage_Core_Model_Variable */
        $variable = Mage::getModel('Mage_Core_Model_Variable');
        if ($variableId) {
            $variable->setStoreId($storeId)
                ->load($variableId);
        }
        Mage::register('current_variable', $variable);
        return $variable;
    }

    /**
     * Index Action
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Custom Variables'));

        $this->_initLayout()
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Variable'))
            ->renderLayout();
    }

    /**
     * New Action (forward to edit action)
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit Action
     *
     */
    public function editAction()
    {
        $variable = $this->_initVariable();

        $this->_title($variable->getId() ? $variable->getCode() : $this->__('New Variable'));

        $this->_initLayout()
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Variable_Edit'))
            ->_addJs($this->getLayout()->createBlock('Mage_Core_Block_Template', '', array(
                'template' => 'Mage_Adminhtml::system/variable/js.phtml'
            )))
            ->renderLayout();
    }

    /**
     * Validate Action
     *
     */
    public function validateAction()
    {
        $response = new Varien_Object(array('error' => false));
        $variable = $this->_initVariable();
        $variable->addData($this->getRequest()->getPost('variable'));
        $result = $variable->validate();
        if ($result !== true && is_string($result)) {
            $this->_getSession()->addError($result);
            $this->_initLayoutMessages('Mage_Adminhtml_Model_Session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }
        $this->getResponse()->setBody($response->toJson());
    }

    /**
     * Save Action
     *
     */
    public function saveAction()
    {
        $variable = $this->_initVariable();
        $data = $this->getRequest()->getPost('variable');
        $back = $this->getRequest()->getParam('back', false);
        if ($data) {
            $data['variable_id'] = $variable->getId();
            $variable->setData($data);
            try {
                $variable->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The custom variable has been saved.')
                );
                if ($back) {
                    $this->_redirect('*/*/edit', array('_current' => true, 'variable_id' => $variable->getId()));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('_current' => true, ));
                return;
            }
        }
        $this->_redirect('*/*/', array());
        return;
    }

    /**
     * Delete Action
     *
     */
    public function deleteAction()
    {
        $variable = $this->_initVariable();
        if ($variable->getId()) {
            try {
                $variable->delete();
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('The custom variable has been deleted.')
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('_current' => true, ));
                return;
            }
        }
        $this->_redirect('*/*/', array());
        return;
    }

    /**
     * WYSIWYG Plugin Action
     *
     */
    public function wysiwygPluginAction()
    {
        $customVariables = Mage::getModel('Mage_Core_Model_Variable')->getVariablesOptionArray(true);
        $storeContactVariabls = Mage::getModel('Mage_Core_Model_Source_Email_Variables')->toOptionArray(true);
        $variables = array($storeContactVariabls, $customVariables);
        $this->getResponse()->setBody(Zend_Json::encode($variables));
    }

    /**
     * Check current user permission
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Adminhtml::variable');
    }
}
