<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomer\Api\ConfigInterface;
use Magento\LoginAsCustomer\Api\DeleteOutdatedSecretsInterface;

/**
 * @inheritdoc
 */
class DeleteOutdatedSecrets implements DeleteOutdatedSecretsInterface
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param ConfigInterface $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ConfigInterface $config
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $timePoint = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - $this->config->getSecretExpirationTime());

        $connection->delete(
            $tableName,
            [
                'created_at < ?' => $timePoint
            ]
        );
    }
}
