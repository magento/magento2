<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Checks if customer is active by customer id
 */
class GetIsCustomerEnabled
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
     * Get is Customer enabled by customer id
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_entity');
        $select = $connection->select()
            ->from(
                $tableName,
                'is_active'
            )
            ->where('entity_id = ?', $customerId);

        return !!$connection->fetchOne($select);
    }
}
