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
 * Customer groups controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Customer_GroupController extends Mage_Adminhtml_Controller_Action
{
    protected function _initGroup()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Groups'));

        Mage::register('current_group', Mage::getModel('Mage_Customer_Model_Group'));
        $groupId = $this->getRequest()->getParam('id');
        if (!is_null($groupId)) {
            Mage::registry('current_group')->load($groupId);
        }

    }
    /**
     * Customer groups list.
     */
    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Groups'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Customer::customer_group');
        $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('Customers'), Mage::helper('Mage_Customer_Helper_Data')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('Customer Groups'), Mage::helper('Mage_Customer_Helper_Data')->__('Customer Groups'));
        $this->renderLayout();
    }

    /**
     * Edit or create customer group.
     */
    public function newAction()
    {
        $this->_initGroup();
        $this->loadLayout();
        $this->_setActiveMenu('Mage_Customer::customer_group');
        $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('Customers'), Mage::helper('Mage_Customer_Helper_Data')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('Customer Groups'), Mage::helper('Mage_Customer_Helper_Data')->__('Customer Groups'), $this->getUrl('*/customer_group'));

        $currentGroup = Mage::registry('current_group');

        if (!is_null($currentGroup->getId())) {
            $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('Edit Group'), Mage::helper('Mage_Customer_Helper_Data')->__('Edit Customer Groups'));
        } else {
            $this->_addBreadcrumb(Mage::helper('Mage_Customer_Helper_Data')->__('New Group'), Mage::helper('Mage_Customer_Helper_Data')->__('New Customer Groups'));
        }

        $this->_title($currentGroup->getId() ? $currentGroup->getCode() : $this->__('New Group'));

        $this->getLayout()->addBlock('Mage_Adminhtml_Block_Customer_Group_Edit', 'group', 'content')
            ->setEditMode((bool)Mage::registry('current_group')->getId());

        $this->renderLayout();
    }

    /**
     * Edit customer group action. Forward to new action.
     */
    public function editAction()
    {
        $this->_forward('new');
    }

    /**
     * Create or save customer group.
     */
    public function saveAction()
    {
        $customerGroup = Mage::getModel('Mage_Customer_Model_Group');
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $customerGroup->load((int)$id);
        }

        $taxClass = (int)$this->getRequest()->getParam('tax_class');

        if ($taxClass) {
            try {
                $customerGroupCode = (string)$this->getRequest()->getParam('code');

                if (!empty($customerGroupCode)) {
                    $customerGroup->setCode($customerGroupCode);
                }

                $customerGroup->setTaxClassId($taxClass)->save();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Customer_Helper_Data')->__('The customer group has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }
    }

    /**
     * Delete customer group action
     */
    public function deleteAction()
    {
        $customerGroup = Mage::getModel('Mage_Customer_Model_Group');
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $customerGroup->load($id);
                $customerGroup->delete();
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Customer_Helper_Data')->__('The customer group has been deleted.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        }

        $this->_redirect('*/customer_group');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Customer::group');
    }
}
