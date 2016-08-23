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
     * Plugin constructor.
     *
     * @param \Magento\AdminNotification\Model\System\MessageFactory $messageFactory
     * @param BulkStatusInterface $bulkStatus
     * @param BulkNotificationManagement $bulkNotificationManagement
     * @param UserContextInterface $userContext
     * @param Details $operationDetails
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\AdminNotification\Model\System\MessageFactory $messageFactory,
        BulkStatusInterface $bulkStatus,
        BulkNotificationManagement $bulkNotificationManagement,
        UserContextInterface $userContext,
        Details $operationDetails,
        AuthorizationInterface $authorization
    ) {
        $this->messageFactory = $messageFactory;
        $this->bulkStatus = $bulkStatus;
        $this->userContext = $userContext;
        $this->operationDetails = $operationDetails;
        $this->bulkNotificationManagement = $bulkNotificationManagement;
        $this->authorization = $authorization;
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
        $userBulks = $this->bulkNotificationManagement->getIgnoredBulksByUser($this->userContext->getUserId());
        $bulkMessages = [];
        foreach ($userBulks as $bulk) {
            $bulkUuid = $bulk->getBulkId();
            $text = $this->getText($this->operationDetails->getDetails($bulkUuid));
            $data = [
                'data' => [
                    'text' => __('Task "%1": ', $bulk->getDescription()) . $text,
                    'severity' => MessageInterface::SEVERITY_MAJOR,
                    'identity' => md5('bulk' . $bulkUuid),
                    'uuid' => $bulkUuid,
                    'status' => $this->bulkStatus->getBulkStatus($bulkUuid),
                    'created_at' => $bulk->getStartTime()
                ]
            ];
            $bulkMessages[] = $this->messageFactory->create($data)->toArray();
        }

        if (!empty($bulkMessages)) {
            $result['totalRecords'] += count($bulkMessages);

            //sort messages by status
            usort(
                $bulkMessages,
                function ($firstBulks, $secondBulk) {
                    if ($firstBulks['status'] === $secondBulk['status']) {
                        return strtotime($firstBulks['created_at']) > strtotime($secondBulk['created_at']) ? -1 : 1;
                    }
                    return $firstBulks['status'] > $secondBulk['status'] ? -1 : 1;
                }
            );
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
            ) ;
        }

        if ($operationDetails['operations_failed'] > 0) {
            $summaryReport .= '<strong>'
                . __('%1 item(s) failed to update', $operationDetails['operations_failed'])
                . '</strong>';
        }
        return $summaryReport;
    }
}
