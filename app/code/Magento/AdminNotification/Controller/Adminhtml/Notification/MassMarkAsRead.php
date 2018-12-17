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
 * Class MassMarkAsRead
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\Notification
 */
class MassMarkAsRead extends Notification
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::mark_as_read';

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
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('notification');
        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select messages.'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = $this->inbox->load($id);
                    if ($model->getId()) {
                        $model->setIsRead(1)->save();
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been marked as Read.', count($ids))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __("We couldn't mark the notification as Read because of an error.")
                );
            }
        }
        $this->_redirect('adminhtml/*/');
    }
}
