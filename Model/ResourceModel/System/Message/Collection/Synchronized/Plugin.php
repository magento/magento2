<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model\ResourceModel\System\Message\Collection\Synchronized;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Notification\MessageInterface;
use Magento\AsynchronousOperations\Model\BulkNotificationManagement;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\Framework\AuthorizationInterface;
use Magento\AsynchronousOperations\Model\StatusMapper;
use Magento\Framework\Bulk\BulkSummaryInterface;

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
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

    /**
     * Plugin constructor.
     *
     * @param \Magento\AdminNotification\Model\System\MessageFactory $messageFactory
     * @param BulkStatusInterface $bulkStatus
     * @param BulkNotificationManagement $bulkNotificationManagement
     * @param UserContextInterface $userContext
     * @param Details $operationDetails
     * @param AuthorizationInterface $authorization
     * @param StatusMapper $statusMapper
     */
    public function __construct(
        \Magento\AdminNotification\Model\System\MessageFactory $messageFactory,
        BulkStatusInterface $bulkStatus,
        BulkNotificationManagement $bulkNotificationManagement,
        UserContextInterface $userContext,
        Details $operationDetails,
        AuthorizationInterface $authorization,
        StatusMapper $statusMapper
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
                $operationDetails = $this->operationDetails->getDetails($bulkUuid);
                $text = $this->getText($operationDetails);
                $bulkStatus = $this->statusMapper->operationStatusToBulkSummaryStatus($bulk->getStatus());
                if ($bulkStatus === BulkSummaryInterface::IN_PROGRESS) {
                    $text = __(
                        '%1 item(s) are currently being updated.',
                            $operationDetails['operations_total']
                        ) . $text;
                }
                $data = [
                    'data' => [
                        'text' => __('Task "%1": ', $bulk->getDescription()) . $text,
                        'severity' => MessageInterface::SEVERITY_MAJOR,
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
            return __('%1 item(s) are currently being updated.', $operationDetails['operations_total']);
        }

        $summaryReport = '';
        if ($operationDetails['operations_successful'] > 0) {
            $summaryReport .= __(
                '%1 item(s) have been successfully updated.',
                $operationDetails['operations_successful']
            ) ;
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
