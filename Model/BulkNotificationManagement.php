<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\AsynchronousOperations\Model\ResourceModel\Bulk\CollectionFactory as BulkCollectionFactory;
use Magento\Framework\Data\Collection;

/**
 * Class for bulk notification manager
 * @since 2.2.0
 */
class BulkNotificationManagement
{
    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var BulkCollectionFactory
     * @since 2.2.0
     */
    private $bulkCollectionFactory;

    /**
     * BulkManagement constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param BulkCollectionFactory $bulkCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        BulkCollectionFactory $bulkCollectionFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->bulkCollectionFactory = $bulkCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Mark given bulks as acknowledged.
     * Notifications related to these bulks will not appear in notification area.
     *
     * @param array $bulkUuids
     * @return bool true on success or false on failure
     * @since 2.2.0
     */
    public function acknowledgeBulks(array $bulkUuids)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        try {
            $connection->insertArray(
                $this->resourceConnection->getTableName('magento_acknowledged_bulk'),
                ['bulk_uuid'],
                $bulkUuids
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Remove given bulks from acknowledged list.
     * Notifications related to these bulks will appear again in notification area.
     *
     * @param array $bulkUuids
     * @return bool true on success or false on failure
     * @since 2.2.0
     */
    public function ignoreBulks(array $bulkUuids)
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        try {
            $connection->delete(
                $this->resourceConnection->getTableName('magento_acknowledged_bulk'),
                ['bulk_uuid IN(?)' => $bulkUuids]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Retrieve all bulks that were acknowledged by given user.
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 2.2.0
     */
    public function getAcknowledgedBulksByUser($userId)
    {
        $bulks = $this->bulkCollectionFactory->create()
            ->join(
                ['acknowledged_bulk' => $this->resourceConnection->getTableName('magento_acknowledged_bulk')],
                'main_table.uuid = acknowledged_bulk.bulk_uuid',
                []
            )->addFieldToFilter('user_id', $userId)
            ->addOrder('start_time', Collection::SORT_ORDER_DESC)
            ->getItems();

        return $bulks;
    }

    /**
     * Retrieve all bulks that were not acknowledged by given user.
     *
     * @param int $userId
     * @return BulkSummaryInterface[]
     * @since 2.2.0
     */
    public function getIgnoredBulksByUser($userId)
    {
        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Bulk\Collection $bulkCollection */
        $bulkCollection = $this->bulkCollectionFactory->create();
        $bulkCollection->getSelect()->joinLeft(
            ['acknowledged_bulk' => $this->resourceConnection->getTableName('magento_acknowledged_bulk')],
            'main_table.uuid = acknowledged_bulk.bulk_uuid',
            ['acknowledged_bulk.bulk_uuid']
        );
        $bulks = $bulkCollection->addFieldToFilter('user_id', $userId)
            ->addFieldToFilter('acknowledged_bulk.bulk_uuid', ['null' => true])
            ->addOrder('start_time', Collection::SORT_ORDER_DESC)
            ->getItems();

        return $bulks;
    }
}
