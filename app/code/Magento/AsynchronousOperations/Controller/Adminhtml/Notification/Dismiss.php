<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Notification;

use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Bulk Notification Dismiss Controller
 */
class Dismiss extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations';

    /**
     * @var BulkNotificationManagement
     */
    private $notificationManagement;

    /**
     * @param Context $context
     * @param BulkNotificationManagement $notificationManagement
     */
    public function __construct(Context $context, BulkNotificationManagement $notificationManagement)
    {
        parent::__construct($context);
        $this->notificationManagement = $notificationManagement;
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

        /** @var Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$isAcknowledged) {
            $result->setHttpResponseCode(400);
        }

        return $result;
    }
}
