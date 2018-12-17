<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Exception;
use Magento\AdminNotification\Controller\Adminhtml\Notification;
use Magento\AdminNotification\Model\NotificationService;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MarkAsRead
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\Notification
 */
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
    private $notfication;

    /**
     * MarkAsRead constructor.
     * @param Action\Context $context
     * @param NotificationService $inbox
     */
    public function __construct(
        Action\Context $context,
        NotificationService $inbox
    ) {
        $this->notfication = $inbox;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $notificationId = (int)$this->getRequest()->getParam('id');
        if ($notificationId) {
            try {
                $this->notfication->markAsRead($notificationId);
                $this->messageManager->addSuccessMessage(__('The message has been marked as Read.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
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
