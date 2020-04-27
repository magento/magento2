<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomer\Api\DeleteOldSecretsInterface;
use Magento\LoginAsCustomer\Model\Config;

/**
 * @api
 */
class DeleteOldSecrets implements DeleteOldSecretsInterface
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
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
    }

    /**
     * Delete old secret key records
     */
    public function execute():void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $timePoint = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - Config::TIME_FRAME);

        $connection->delete(
            $tableName,
            [
                'created_at < ?' => $timePoint
            ]
        );
    }
}
