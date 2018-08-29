<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized;

/**
 * Class Plugin to add bulks related notification messages to Synchronized Collection
 */
class Plugin
{
    /**
     * @var \Magento\AdminNotification\Model\System\MessageFactory
     */
    private $messageFactory;

    /**
     * @var \Magento\Framework\Bulk\BulkStatusInterface
     */
    private $bulkStatus;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\AsynchronousOperations\Model\Operation\Details
     */
    private $operationDetails;

    /**
     * @var \Magento\AsynchronousOperations\Model\BulkNotificationManagement
     */
    private $bulkNotificationManagement;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\AsynchronousOperations\Model\StatusMapper
     */
    private $statusMapper;

    /**
     * Plugin constructor.
     *
     * @param \Magento\AdminNotification\Model\System\MessageFactory $messageFactory
     * @param \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus
     * @param \Magento\AsynchronousOperations\Model\BulkNotificationManagement $bulkNotificationManagement
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\AsynchronousOperations\Model\StatusMapper $statusMapper
     */
    public function __construct(
        \Magento\AdminNotification\Model\System\MessageFactory $messageFactory,
        \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus,
        \Magento\AsynchronousOperations\Model\BulkNotificationManagement $bulkNotificationManagement,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\AsynchronousOperations\Model\Operation\Details $operationDetails,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\AsynchronousOperations\Model\StatusMapper $statusMapper
    ) {
        $this->messageFactory = $messageFactory;
        $this->bulkStatus = $bulkStatus;
        $this->userContext = $userContext;
        $this->operationDetails = $operationDetails;
        $this->bulkNotificationManagement = $bulkNotificationManagement;
        $this->authorization = $authorization;
        $this->statusMapper = $statusMapper;
    }

    /**
     * Adding bulk related messages to notification area
     *
     * @param \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized $collection
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToArray(
        \Magento\AdminNotification\Model\ResourceModel\System\Message\Collection\Synchronized $collection,
        $result
    ) {
        if (!$this->authorization->isAllowed('Magento_Logging::system_magento_logging_bulk_operations')) {
            return $result;
        }
        $userId = $this->userContext->getUserId();
        $userBulks = $this->bulkStatus->getBulksByUser($userId);
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
                if ($bulkStatus === \Magento\Framework\Bulk\BulkSummaryInterface::IN_PROGRESS) {
                    $text = __('%1 item(s) are currently being updated.', $details['operations_total']) . $text;
                }
                $data = [
                    'data' => [
                        'text' => __('Task "%1": ', $bulk->getDescription()) . $text,
                        'severity' => \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR,
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
}
