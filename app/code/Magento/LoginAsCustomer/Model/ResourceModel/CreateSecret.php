<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Math\Random;
use Magento\LoginAsCustomer\Api\CreateSecretInterface;

/**
 * @api
 */
class CreateSecret implements CreateSecretInterface
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
     * @var Random
     */
    private $random;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        Random $random
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dateTime = $dateTime;
        $this->random = $random;
    }

    /**
     * Create a new secret key
     * @return string
     * @param int $customerId
     * @param int $adminId
     */
    public function execute(int $customerId, int $adminId):string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $secret = $this->random->getRandomString(64);

        $connection->insert(
            $tableName,
            [
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'secret' => $secret,
                'created_at' => $this->dateTime->gmtDate(),
            ]
        );

        return $secret;
    }
}
