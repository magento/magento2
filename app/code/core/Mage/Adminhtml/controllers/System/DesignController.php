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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Adminhtml_System_DesignController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Design'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Adminhtml::system');
        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Design'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Design_Grid')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Design'));

        $this->loadLayout();
        $this->_setActiveMenu('Mage_Adminhtml::system');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $id  = (int) $this->getRequest()->getParam('id');
        $design    = Mage::getModel('Mage_Core_Model_Design');

        if ($id) {
            $design->load($id);
        }

        $this->_title($design->getId() ? $this->__('Edit Design Change') : $this->__('New Design Change'));

        Mage::register('design', $design);

        $this->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Design_Edit'));
        $this->_addLeft($this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Design_Edit_Tabs', 'design_tabs'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $id = (int) $this->getRequest()->getParam('id');

            $design = Mage::getModel('Mage_Core_Model_Design');
            if ($id) {
                $design->load($id);
            }

            $design->setData($data['design']);
            if ($id) {
                $design->setId($id);
            }
            try {
                $design->save();

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('The design change has been saved.'));
            } catch (Exception $e){
                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addError($e->getMessage())
                    ->setDesignData($data);
                $this->_redirect('*/*/edit', array('id'=>$design->getId()));
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $design = Mage::getModel('Mage_Core_Model_Design')->load($id);

            try {
                $design->delete();

                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addSuccess($this->__('The design change has been deleted.'));
            } catch (Mage_Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addException($e, $this->__("Cannot delete the design change."));
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/*/'));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Adminhtml::design');
    }
}
