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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml AdminNotification controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_NotificationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Notifications'));

        $this->loadLayout()
            ->_setActiveMenu('system/notification')
            ->_addBreadcrumb(Mage::helper('Mage_AdminNotification_Helper_Data')->__('Messages Inbox'), Mage::helper('Mage_Adminhtml_Helper_Data')->__('Messages Inbox'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Notification_Inbox'))
            ->renderLayout();
    }

    public function markAsReadAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            $model = Mage::getModel('Mage_AdminNotification_Model_Inbox')
                ->load($id);

            if (!$model->getId()) {
                $session->addError(Mage::helper('Mage_AdminNotification_Helper_Data')->__('Unable to proceed. Please, try again.'));
                $this->_redirect('*/*/');
                return ;
            }

            try {
                $model->setIsRead(1)
                    ->save();
                $session->addSuccess(Mage::helper('Mage_AdminNotification_Helper_Data')->__('The message has been marked as read.'));
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_AdminNotification_Helper_Data')->__('An error occurred while marking notification as read.'));
            }

            $this->_redirectReferer();
            return;
        }
        $this->_redirect('*/*/');
    }

    public function massMarkAsReadAction()
    {
        $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $session->addError(Mage::helper('Mage_AdminNotification_Helper_Data')->__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('Mage_AdminNotification_Model_Inbox')
                        ->load($id);
                    if ($model->getId()) {
                        $model->setIsRead(1)
                            ->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_AdminNotification_Helper_Data')->__('Total of %d record(s) have been marked as read.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_AdminNotification_Helper_Data')->__('An error occurred while marking the messages as read.'));
            }
        }
        $this->_redirect('*/*/');
    }

    public function removeAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            $model = Mage::getModel('Mage_AdminNotification_Model_Inbox')
                ->load($id);

            if (!$model->getId()) {
                $this->_redirect('*/*/');
                return ;
            }

            try {
                $model->setIsRemove(1)
                    ->save();
                $session->addSuccess(Mage::helper('Mage_AdminNotification_Helper_Data')->__('The message has been removed.'));
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_AdminNotification_Helper_Data')->__('An error occurred while removing the message.'));
            }

            $this->_redirect('*/*/');
            return;
        }
        $this->_redirect('*/*/');
    }

    public function massRemoveAction()
    {
        $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $session->addError(Mage::helper('Mage_AdminNotification_Helper_Data')->__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getModel('Mage_AdminNotification_Model_Inbox')
                        ->load($id);
                    if ($model->getId()) {
                        $model->setIsRemove(1)
                            ->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    Mage::helper('Mage_AdminNotification_Helper_Data')->__('Total of %d record(s) have been removed.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, Mage::helper('Mage_AdminNotification_Helper_Data')->__('An error occurred while removing messages.'));
            }
        }
        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'markAsRead':
                $acl = 'system/adminnotification/mark_as_read';
                break;

            case 'massMarkAsRead':
                $acl = 'system/adminnotification/mark_as_read';
                break;

            case 'remove':
                $acl = 'system/adminnotification/remove';
                break;

            case 'massRemove':
                $acl = 'system/adminnotification/remove';
                break;

            default:
                $acl = 'system/adminnotification/show_list';
        }
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed($acl);
    }
}
