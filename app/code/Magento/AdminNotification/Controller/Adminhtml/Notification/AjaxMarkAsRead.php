<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Controller\Adminhtml\Notification;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class AjaxMarkAsRead extends \Magento\AdminNotification\Controller\Adminhtml\Notification
{
    /**
     * @var \Magento\AdminNotification\Model\NotificationService
     */
    private $notificationService;

    /**
     * @param Action\Context $context
     * @param \Magento\AdminNotification\Model\NotificationService|null $notificationService
     * @throws \RuntimeException
     */
    public function __construct(
        Action\Context $context,
        \Magento\AdminNotification\Model\NotificationService $notificationService = null
    ) {
        parent::__construct($context);
        $this->notificationService = $notificationService?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\AdminNotification\Model\NotificationService::class);
    }

    /**
     * Mark notification as read (AJAX action)
     *
     * @return \Magento\Framework\Controller\Result\Json|void
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        if (!$this->getRequest()->getPostValue()) {
            return;
        }
        $notificationId = (int)$this->getRequest()->getPost('id');
        $responseData = [];
        try {
            $this->notificationService->markAsRead($notificationId);
            $responseData['success'] = true;
        } catch (\Exception $e) {
            $responseData['success'] = false;
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}
