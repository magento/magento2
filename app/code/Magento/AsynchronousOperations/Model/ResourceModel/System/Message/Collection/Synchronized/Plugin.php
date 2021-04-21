<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized;

use Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized;
use Magento\AdminNotification\Model\System\MessageFactory;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\AsynchronousOperations\Model\StatusMapper;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\Framework\Bulk\GetBulksByUserAndTypeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Notification\MessageInterface;

/**
 * Class Plugin to add bulks related notification messages to Synchronized Collection
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plugin
{
    private const BULK_LOGGING_ACL = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations";

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var BulkStatusInterface
     */
    private $bulkStatus;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var Details
     */
    private $operationDetails;

    /**
     * @var BulkNotificationManagement
     */
    private $bulkNotificationManagement;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

    /**
     * @var AuthorizationInterface|mixed|null
     */
    private $authorization;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var GetBulksByUserAndTypeInterface|null
     */
    private $getBulksByUserAndType;

    /**
     * @param MessageFactory $messageFactory
     * @param BulkStatusInterface $bulkStatus
     * @param BulkNotificationManagement $bulkNotificationManagement
     * @param UserContextInterface $userContext
     * @param Details $operationDetails
     * @param AuthorizationInterface $authorization
     * @param StatusMapper $statusMapper
     * @param Encryptor|null $encryptor
     * @param GetBulksByUserAndTypeInterface|null $getBulksByUserAndType
     */
    public function __construct(
        MessageFactory $messageFactory,
        BulkStatusInterface $bulkStatus,
        BulkNotificationManagement $bulkNotificationManagement,
        UserContextInterface $userContext,
        Details $operationDetails,
        AuthorizationInterface $authorization,
        StatusMapper $statusMapper,
        ?Encryptor $encryptor = null,
        ?GetBulksByUserAndTypeInterface $getBulksByUserAndType = null
    ) {
        $this->messageFactory = $messageFactory;
        $this->bulkStatus = $bulkStatus;
        $this->userContext = $userContext;
        $this->operationDetails = $operationDetails;
        $this->bulkNotificationManagement = $bulkNotificationManagement;
        $this->authorization = $authorization;
        $this->statusMapper = $statusMapper;
        $this->encryptor = $encryptor ?: ObjectManager::getInstance()->get(Encryptor::class);
        $this->getBulksByUserAndType = $getBulksByUserAndType
            ?: ObjectManager::getInstance()->get(GetBulksByUserAndTypeInterface::class);
    }

    /**
     * Adding bulk related messages to notification area
     *
     * @param Synchronized $collection
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToArray(Synchronized $collection, $result)
    {
        if (!$this->isAllowed()) {
            return $result;
        }
        $userId = (int) $this->userContext->getUserId();
        $userType = (int) $this->userContext->getUserType();
        $userBulks = $this->getBulksByUserAndType->execute($userId, $userType);
        $acknowledgedBulks = $this->getAcknowledgedBulksUuid(
            $this->bulkNotificationManagement->getAcknowledgedBulksByUser($userId)
        );
        $bulkMessages = [];
        foreach ($userBulks as $bulk) {
            $bulkUuid = $bulk->getBulkId();
            if (!in_array($bulkUuid, $acknowledgedBulks)) {
                $details = $this->operationDetails->getDetails($bulkUuid);
                $text = $this->getText($details);
                $bulkStatus = $this->statusMapper->operationStatusToBulkSummaryStatus($bulk->getStatus());
                if ($bulkStatus === BulkSummaryInterface::IN_PROGRESS) {
                    $text = __('%1 item(s) are currently being updated.', $details['operations_total']) . $text;
                }
                $data = [
                    'data' => [
                        'text' => __('Task "%1": ', $bulk->getDescription()) . $text,
                        'severity' => MessageInterface::SEVERITY_MAJOR,
                        // md5() here is not for cryptographic use.
                        // phpcs:ignore Magento2.Security.InsecureFunction
                        'identity' => md5('bulk' . $bulkUuid),
                        'uuid' => $bulkUuid,
                        'status' => $bulkStatus,
                        'created_at' => $bulk->getStartTime()
                    ]
                ];
                $bulkMessages[] = $this->messageFactory->create($data)->toArray();
            }
        }

        if (!empty($bulkMessages)) {
            $result['totalRecords'] += count($bulkMessages);
            $bulkMessages = array_slice($bulkMessages, 0, 5);
            $result['items'] = array_merge($bulkMessages, $result['items']);
        }

        return $result;
    }

    /**
     * Get Bulk notification message
     *
     * @param array $operationDetails
     * @return \Magento\Framework\Phrase|string
     */
    private function getText($operationDetails)
    {
        if (0 == $operationDetails['operations_successful'] && 0 == $operationDetails['operations_failed']) {
            return __('%1 item(s) have been scheduled for update.', $operationDetails['operations_total']);
        }

        $summaryReport = '';
        if ($operationDetails['operations_successful'] > 0) {
            $summaryReport .= __(
                '%1 item(s) have been successfully updated.',
                $operationDetails['operations_successful']
            );
        }

        if ($operationDetails['operations_failed'] > 0) {
            $summaryReport .= '<strong>'
                . __('%1 item(s) failed to update', $operationDetails['operations_failed'])
                . '</strong>';
        }
        return $summaryReport;
    }

    /**
     * Get array with acknowledgedBulksUuid
     *
     * @param array $acknowledgedBulks
     * @return array
     */
    private function getAcknowledgedBulksUuid($acknowledgedBulks)
    {
        $acknowledgedBulksArray = [];
        foreach ($acknowledgedBulks as $bulk) {
            $acknowledgedBulksArray[] = $bulk->getBulkId();
        }
        return $acknowledgedBulksArray;
    }

    /**
     * Check if it allowed to see bulk operations.
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return $this->authorization->isAllowed(self::BULK_LOGGING_ACL);
    }
}
