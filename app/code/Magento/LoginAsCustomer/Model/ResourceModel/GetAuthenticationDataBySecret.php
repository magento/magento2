<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomerApi\Api\ConfigInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;

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
     * @var AuthenticationDataInterfaceFactory
     */
    private $authenticationDataFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param ConfigInterface $config
     * @param AuthenticationDataInterfaceFactory $authenticationDataFactory
     * @param EncryptorInterface|null $encryptor
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        ConfigInterface $config,
        AuthenticationDataInterfaceFactory $authenticationDataFactory,
        ?EncryptorInterface $encryptor = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->authenticationDataFactory = $authenticationDataFactory;
        $this->encryptor = $encryptor ?? ObjectManager::getInstance()->get(EncryptorInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(string $secret): AuthenticationDataInterface
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $timePoint = date(
            'Y-m-d H:i:s',
            $this->dateTime->gmtTimestamp() - $this->config->getAuthenticationDataExpirationTime()
        );

        $hash = $this->encryptor->hash($secret);

        $select = $connection->select()
            ->from(['main_table' => $tableName])
            ->where('main_table.secret = ?', $hash)
            ->where('main_table.created_at > ?', $timePoint);

        $data = $connection->fetchRow($select);

        if (!$data) {
            throw new LocalizedException(__('Secret key is not found or was expired.'));
        }

        /** @var AuthenticationDataInterface $authenticationData */
        $authenticationData = $this->authenticationDataFactory->create(
            [
                'customerId' => (int)$data['customer_id'],
                'adminId' => (int)$data['admin_id'],
                'extensionAttributes' => null,
            ]
        );
        return $authenticationData;
    }
}
