<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\AdminNotification\Controller\Adminhtml\Notification;
use Magento\AdminNotification\Model\NotificationService;
use Magento\Backend\App\Action;

class MarkAsRead extends Notification
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::mark_as_read';

    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(Action\Context $context, NotificationService $notificationService)
    {
        parent::__construct($context);
        $this->notificationService = $notificationService;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $notificationId = (int)$this->getRequest()->getParam('id');
        if ($notificationId) {
            try {
                $this->notificationService->markAsRead($notificationId);
                $this->messageManager->addSuccessMessage(__('The message has been marked as Read.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }

            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        }
        $this->_redirect('adminhtml/*/');
    }
}
