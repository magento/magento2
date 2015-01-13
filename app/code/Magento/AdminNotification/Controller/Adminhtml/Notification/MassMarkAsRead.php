<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class MassMarkAsRead extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{
    /**
     * @return void
     */
    public function execute()
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
}
