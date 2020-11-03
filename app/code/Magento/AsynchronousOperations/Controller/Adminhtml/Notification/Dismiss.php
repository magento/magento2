<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Notification;

use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\AsynchronousOperations\Model\AccessManager;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Class Bulk Notification Dismiss Controller
 */
class Dismiss extends Action implements HttpGetActionInterface
{
    /**
     * @var BulkNotificationManagement
     */
    private $notificationManagement;

    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * Class constructor.
     *
     * @param Context $context
     * @param BulkNotificationManagement $notificationManagement
     * @param AccessManager $accessManager
     */
    public function __construct(
        Context $context,
        BulkNotificationManagement $notificationManagement,
        AccessManager $accessManager
    ) {
        parent::__construct($context);
        $this->notificationManagement = $notificationManagement;
        $this->accessManager = $accessManager;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->accessManager->isOwnActionsAllowed();
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $bulkUuids = [];
        foreach ((array)$this->getRequest()->getParam('uuid', []) as $bulkUuid) {
            $bulkUuids[] = (string)$bulkUuid;
        }

        $isAcknowledged = $this->notificationManagement->acknowledgeBulks($bulkUuids);

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$isAcknowledged) {
            $result->setHttpResponseCode(400);
        }

        return $result;
    }
}
