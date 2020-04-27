<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\LoginAsCustomer\Api\ConfigInterface;
use Magento\LoginAsCustomer\Api\GetAuthenticateDataInterface;

/**
 * @api
 */
class GetAuthenticateData implements GetAuthenticateDataInterface
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
     * Load logic details based on secret key
     * @return array
     * @throws LocalizedException
     * @param string $secretKey
     */
    public function execute(string $secretKey):array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $timePoint = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - $this->config->getSecretExpirationTime());

        $select = $connection->select()
            ->from(['main_table' => $tableName])
            ->where('main_table.secret = ?', $secretKey)
            ->where('main_table.created_at > ?', $timePoint)
            ->limit(1);

        $data = $connection->fetchRow($select);

        if (!$data) {
            throw new LocalizedException(__('Secret key is not valid.'));
        }


        return $data;
    }
}
