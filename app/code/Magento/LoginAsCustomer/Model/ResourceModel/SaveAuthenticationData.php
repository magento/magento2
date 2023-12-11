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
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\GenerateAuthenticationSecretInterface;
use Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface;

/**
 * @inheritdoc
 */
class SaveAuthenticationData implements SaveAuthenticationDataInterface
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var GenerateAuthenticationSecretInterface
     */
    private $generateAuthenticationSecret;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DateTime $dateTime
     * @param Random $random
     * @param EncryptorInterface|null $encryptor
     * @param GenerateAuthenticationSecretInterface|null $generateAuthenticationSecret
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        Random $random,
        ?EncryptorInterface $encryptor = null,
        ?GenerateAuthenticationSecretInterface $generateAuthenticationSecret = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->random = $random;
        $this->encryptor = $encryptor ?? ObjectManager::getInstance()->get(EncryptorInterface::class);
        $this->generateAuthenticationSecret = $generateAuthenticationSecret
            ?? ObjectManager::getInstance()->get(GenerateAuthenticationSecretInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(AuthenticationDataInterface $authenticationData): string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $key = $this->random->getRandomString(64);
        $hash = $this->encryptor->hash($key);

        $connection->insert(
            $tableName,
            [
                'customer_id' => $authenticationData->getCustomerId(),
                'admin_id' => $authenticationData->getAdminId(),
                'secret' => $hash,
                'created_at' => $this->dateTime->gmtDate(),
            ]
        );

        return $this->generateAuthenticationSecret->execute($authenticationData);
    }
}
