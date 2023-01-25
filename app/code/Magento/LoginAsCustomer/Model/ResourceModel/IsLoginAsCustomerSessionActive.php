<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\LoginAsCustomerApi\Api\IsLoginAsCustomerSessionActiveInterface;

/**
 * @inheritdoc
 */
class IsLoginAsCustomerSessionActive implements IsLoginAsCustomerSessionActiveInterface
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
    public function execute(int $customerId, int $userId): bool
    {
        $tableName = $this->resourceConnection->getTableName('login_as_customer');
        $connection = $this->resourceConnection->getConnection();

        $query = $connection->select()
            ->from($tableName)
            ->where('customer_id = ?', $customerId)
            ->where('admin_id = ?', $userId);

        $result = $connection->fetchRow($query);

        return false !== $result;
    }
}
