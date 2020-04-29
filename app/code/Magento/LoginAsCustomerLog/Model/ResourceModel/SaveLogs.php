<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerLog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomerLog\Api\SaveLogsInterface;

/**
 * @inheritDoc
 */
class SaveLogs implements SaveLogsInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     */
    public function __construct(ResourceConnection $resourceConnection, DateTime $dateTime)
    {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $logs): void
    {
        $logsData = [];
        foreach ($logs as $log) {
            if (!$log->getTime()) {
                $log->setTime($this->dateTime->gmtDate());
            }
            $logsData[] = $log->getData();
        }
        $logTable = $this->resourceConnection->getTableName(Log::TABLE_NAME_LOG);
        $connection = $this->resourceConnection->getConnection();
        $connection->insertMultiple($logTable, $logsData);
    }
}
