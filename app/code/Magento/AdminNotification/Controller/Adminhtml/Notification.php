<?php
/**
 * Adminhtml AdminNotification controller
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\AdminNotification\Controller\Adminhtml;

class Notification extends \Magento\Backend\App\AbstractAction
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->_title->add(__('Notifications'));

        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_AdminNotification::system_adminnotification'
        )->_addBreadcrumb(
            __('Messages Inbox'),
            __('Messages Inbox')
        );
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function markAsReadAction()
    {
        $notificationId = (int)$this->getRequest()->getParam('id');
        if ($notificationId) {
            try {
                $this->_objectManager->create(
                    'Magento\AdminNotification\Model\NotificationService'
                )->markAsRead(
                    $notificationId
                );
                $this->messageManager->addSuccess(__('The message has been marked as Read.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }

            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * Mark notification as read (AJAX action)
     *
     * @return void
     */
    public function ajaxMarkAsReadAction()
    {
        if (!$this->getRequest()->getPost()) {
            return;
        }
        $notificationId = (int)$this->getRequest()->getPost('id');
        $responseData = array();
        try {
            $this->_objectManager->create(
                'Magento\AdminNotification\Model\NotificationService'
            )->markAsRead(
                $notificationId
            );
            $responseData['success'] = true;
        } catch (\Exception $e) {
            $responseData['success'] = false;
        }
        $this->getResponse()->representJson(
            $this->_objectManager->create('Magento\Core\Helper\Data')->jsonEncode($responseData)
        );
    }

    /**
     * @return void
     */
    public function massMarkAsReadAction()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')->load($id);
                    if ($model->getId()) {
                        $model->setIsRead(1)->save();
                    }
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been marked as Read.', count($ids))
                );
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return void
     */
    public function removeAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')->load($id);

            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                $model->setIsRemove(1)->save();
                $this->messageManager->addSuccess(__('The message has been removed.'));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __("We couldn't remove the messages because of an error."));
            }

            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return void
     */
    public function massRemoveAction()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->messageManager->addError(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create('Magento\AdminNotification\Model\Inbox')->load($id);
                    if ($model->getId()) {
                        $model->setIsRemove(1)->save();
                    }
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) have been removed.', count($ids)));
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __("We couldn't remove the messages because of an error."));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'markAsRead':
                $acl = 'Magento_AdminNotification::mark_as_read';
                break;

            case 'massMarkAsRead':
                $acl = 'Magento_AdminNotification::mark_as_read';
                break;

            case 'remove':
                $acl = 'Magento_AdminNotification::adminnotification_remove';
                break;

            case 'massRemove':
                $acl = 'Magento_AdminNotification::adminnotification_remove';
                break;

            default:
                $acl = 'Magento_AdminNotification::show_list';
        }
        return $this->_authorization->isAllowed($acl);
    }
}
