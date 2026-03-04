<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Delete Login as Customer assistance allowed record.
 */
class DeleteLoginAsCustomerAssistanceAllowed
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
     * Delete Login as Customer assistance allowed record by Customer id.
     *
     * @param int $customerId
     * @return void
     */
    public function execute(int $customerId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer_assistance_allowed');

        $connection->delete(
            $tableName,
            [
                'customer_id = ?' => $customerId
            ]
        );
    }
}
