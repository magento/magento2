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
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomer\Api\Data\AuthenticationDataInterfaceFactor;
use Magento\LoginAsCustomer\Api\GetAuthenticationDataBySecretInterface;

/**
 * @inheritdoc
 */
class GetAuthenticationDataBySecret implements GetAuthenticationDataBySecretInterface
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
     * @var AuthenticationDataInterfaceFactor
     */
    private $authenticationDataFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ConfigInterface $config,
        AuthenticationDataInterfaceFactory $authenticationDataFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $secretKey): AuthenticationDataInterface
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $timePoint = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - $this->config->getSecretExpirationTime());

        $select = $connection->select()
            ->from(['main_table' => $tableName])
            ->where('main_table.secret = ?', $secretKey)
            ->where('main_table.created_at > ?', $timePoint);

        $data = $connection->fetchRow($select);

        if (!$data) {
            throw new LocalizedException(__('Secret key is not found or was expired.'));
        }

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => (int)$data['admin_id'],
                'adminId' => (int)$data['customer_id'],
                'extensionAttributes' => null,
            ]
        );
        return $authenticationData;
    }
}
