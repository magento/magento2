<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Controller\Adminhtml\Bulk;

use Magento\AsynchronousOperations\Model\BulkManagement;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\AsynchronousOperations\Model\IsAllowedForBulkUuid;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\App\Action;
use Magento\AsynchronousOperations\Model\AccessValidator;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Bulk Retry Controller
 */
class Retry extends Action implements HttpPostActionInterface
{
    /**
     * @var BulkManagement
     */
    private $bulkManagement;

    /**
     * @var BulkNotificationManagement
     */
    private $notificationManagement;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var IsAllowedForBulkUuid
     */
    private $isAllowedForBulkUuid;

    /**
     * @param Context $context
     * @param BulkManagement $bulkManagement
     * @param BulkNotificationManagement $notificationManagement
     * @param AccessValidator $accessValidator
     * @param IsAllowedForBulkUuid|null $isAllowedForBulkUuid
     */
    public function __construct(
        Context $context,
        BulkManagement $bulkManagement,
        BulkNotificationManagement $notificationManagement,
        AccessValidator $accessValidator,
        ?IsAllowedForBulkUuid $isAllowedForBulkUuid = null
    ) {
        parent::__construct($context);
        $this->bulkManagement = $bulkManagement;
        $this->notificationManagement = $notificationManagement;
        $this->accessValidator = $accessValidator;
        $this->isAllowedForBulkUuid = $isAllowedForBulkUuid
            ?: ObjectManager::getInstance()->get(IsAllowedForBulkUuid::class);
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return $this->isAllowedForBulkUuid->execute($this->getRequest()->getParam('uuid'));
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $bulkUuid = $this->getRequest()->getParam('uuid');
        $isAjax = $this->getRequest()->getParam('isAjax');
        $operationsToRetry = (array)$this->getRequest()->getParam('operations_to_retry', []);
        $errorCodes = [];
        foreach ($operationsToRetry as $operationData) {
            if (isset($operationData['error_code'])) {
                $errorCodes[] = (int)$operationData['error_code'];
            }
        }

        $affectedOperations = $this->bulkManagement->retryBulk($bulkUuid, $errorCodes);
        $this->notificationManagement->ignoreBulks([$bulkUuid]);
        if (!$isAjax) {
            $this->messageManager->addSuccessMessage(
                __('%1 item(s) have been scheduled for update."', $affectedOperations)
            );
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $result->setPath('bulk/index');
        } else {
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setHttpResponseCode(200);
            $response = new \Magento\Framework\DataObject();
            $response->setError(0);

            $result->setData($response);
        }
        return $result;
    }
}
