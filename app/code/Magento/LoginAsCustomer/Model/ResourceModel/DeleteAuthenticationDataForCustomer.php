<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForCustomerInterface;

/**
 * @inheritdoc
 */
class DeleteAuthenticationDataForCustomer implements DeleteAuthenticationDataForCustomerInterface
{
    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(string $secret): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer');

        $connection->delete(
            $tableName,
            [
                'secret = ?' => $secret
            ]
        );
    }
}
