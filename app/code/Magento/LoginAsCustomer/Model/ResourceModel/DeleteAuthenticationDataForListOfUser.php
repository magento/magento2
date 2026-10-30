<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForListOfUserInterface;

/**
 * @inheritdoc
 */
class DeleteAuthenticationDataForListOfUser implements DeleteAuthenticationDataForListOfUserInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $userIds): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $connection->delete(
            $tableName,
            [
                'admin_id IN (?)' => $userIds
            ]
        );
    }
}
