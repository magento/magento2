<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Check if customer is Enabled
 */
class IsCustomerEnabled
{
    /**
     * @var array
     */
    private $registry = [];

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
     * Check if Customer is enabled by Customer id.
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        if (!isset($this->registry[$customerId])) {

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('customer_entity');

            $select = $connection->select()
                ->from(
                    $tableName,
                    'is_active'
                )
                ->where('entity_id = ?', $customerId);
            $isActive = !!$connection->fetchOne($select);
            $this->registry[$customerId] = $isActive;
        }

        return $this->registry[$customerId];
    }
}
