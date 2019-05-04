<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;

class BulkCleanup
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * BulkCleanup constructor.
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime\DateTime $time
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $time
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->date = $time;
    }

    /**
     * Remove all expired bulks and corresponding operations
     *
     * @return void
     */
    public function execute()
    {
        $metadata = $this->metadataPool->getMetadata(BulkSummaryInterface::class);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());

        $bulkLifetime = 3600 * 24 * (int)$this->scopeConfig->getValue('system/bulk/lifetime');
        $maxBulkStartTime = $this->dateTime->formatDate($this->date->gmtTimestamp() - $bulkLifetime);
        $connection->delete($metadata->getEntityTable(), ['start_time <= ?' => $maxBulkStartTime]);
    }
}
