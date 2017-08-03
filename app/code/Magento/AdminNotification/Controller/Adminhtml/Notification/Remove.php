<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

/**
 * Class \Magento\AdminNotification\Controller\Adminhtml\Notification\Remove
 *
 * @since 2.0.0
 */
class Remove extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::adminnotification_remove';

    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->_objectManager->create(\Magento\AdminNotification\Model\Inbox::class)->load($id);

            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                $model->setIsRemove(1)->save();
                $this->messageManager->addSuccess(__('The message has been removed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __("We couldn't remove the messages because of an error."));
            }

            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_redirect('adminhtml/*/');
    }
}
