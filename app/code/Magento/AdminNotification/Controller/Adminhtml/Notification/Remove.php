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
use Magento\AdminNotification\Model\Inbox;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Remove
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\Notification
 */
class Remove extends Notification
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::adminnotification_remove';

    /**
     * @var Inbox
     */
    private $inbox;

    /**
     * MarkAsRead constructor.
     * @param Action\Context $context
     * @param Inbox $inbox
     */
    public function __construct(
        Action\Context $context,
        Inbox $inbox
    ) {
        $this->inbox = $inbox;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $messageId = $this->getRequest()->getParam('id');
        if ($messageId) {
            $model = $this->inbox->load($messageId);
            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                $model->setIsRemove(1)->save();
                $this->messageManager->addSuccessMessage(__('The message has been removed.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __("We couldn't remove the messages because of an error.")
                );
            }

            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_redirect('adminhtml/*/');
    }
}
