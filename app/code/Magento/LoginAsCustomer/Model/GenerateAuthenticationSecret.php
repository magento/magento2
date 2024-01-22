<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\LoginAsCustomerApi\Api\GenerateAuthenticationSecretInterface;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;

/**
 * Generates authentication secret
 */
class GenerateAuthenticationSecret implements GenerateAuthenticationSecretInterface
{
    /**#@+
     * Constants
     */
    private const CUSTOMER_ID = 'customer_id';
    private const ADMIN_ID = 'admin_id';
    private const TIME_STAMP = 'time_stamp';
    /**#@-*/

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param DateTime $dateTime
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DateTime $dateTime,
        EncryptorInterface $encryptor,
        SerializerInterface $serializer
    ) {
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function execute(AuthenticationDataInterface $authenticationData): string
    {
        $currentTimestamp = $this->dateTime->timestamp();
        $customerId = $authenticationData->getCustomerId();
        $adminId = $authenticationData->getAdminId();
        return $this->encryptor->encrypt($this->serializer->serialize(
            [
                self::ADMIN_ID => $adminId,
                self::CUSTOMER_ID => $customerId,
                self::TIME_STAMP => $currentTimestamp
            ]
        ));
    }
}
