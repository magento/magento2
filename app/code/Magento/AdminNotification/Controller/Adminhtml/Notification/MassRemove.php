<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

class MassRemove extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::adminnotification_remove';

    /**
     * @return void
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->_objectManager->create(\Magento\AdminNotification\Model\Inbox::class)->load($id);
                    if ($model->getId()) {
                        $model->setIsRemove(1)->save();
                    }
                }
                $this->messageManager->addSuccessMessage(__('Total of %1 record(s) have been removed.', count($ids)));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager
                    ->addExceptionMessage($e, __("We couldn't remove the messages because of an error."));
            }
        }
        $this->_redirect('adminhtml/*/');
    }
}
