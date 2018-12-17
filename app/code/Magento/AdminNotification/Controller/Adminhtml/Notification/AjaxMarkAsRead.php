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
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class AjaxMarkAsRead
 *
 * @package Magento\AdminNotification\Controller\Adminhtml\Notification
 */
class AjaxMarkAsRead extends Notification
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @param Action\Context $context
     * @param NotificationService|null $notificationService
     * @throws \RuntimeException
     */
    public function __construct(
        Action\Context $context,
        NotificationService $notificationService = null
    ) {
        parent::__construct($context);
        $this->notificationService = $notificationService ?: ObjectManager::getInstance()
            ->get(NotificationService::class);
    }

    /**
     * Mark notification as read (AJAX action)
     *
     * @return Json|null
     * @throws \InvalidArgumentException
     */
    public function execute(): ?Json
    {
        if (!$this->getRequest()->getPostValue()) {
            return null;
        }
        $notificationId = (int)$this->getRequest()->getPost('id');
        $responseData = [];
        try {
            $this->notificationService->markAsRead($notificationId);
            $responseData['success'] = true;
        } catch (Exception $e) {
            $responseData['success'] = false;
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}
