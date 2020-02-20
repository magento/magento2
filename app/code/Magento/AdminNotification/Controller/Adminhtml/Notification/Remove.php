<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\AdminNotification\Controller\Adminhtml\Notification;
use Magento\AdminNotification\Model\InboxFactory as InboxModelFactory;
use Magento\Backend\App\Action;

class Remove extends Notification
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_AdminNotification::adminnotification_remove';

    /**
     * @var InboxModelFactory
     */
    private $inboxModelFactory;

    public function __construct(Action\Context $context, InboxModelFactory $inboxModelFactory)
    {
        parent::__construct($context);
        $this->inboxModelFactory = $inboxModelFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            $model = $this->inboxModelFactory->create()->load($id);

            if (!$model->getId()) {
                $this->_redirect('adminhtml/*/');
                return;
            }

            try {
                $model->setIsRemove(1)->save();
                $this->messageManager->addSuccessMessage(__('The message has been removed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
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
