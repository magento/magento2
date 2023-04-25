<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\ResourceModel\Group;

use Magento\Framework\App\ResourceConnection;

/**
 * Resource model for customer group resolver service
 */
class Resolver
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
     * Resolve customer group from db
     *
     * @param int $customerId
     * @return int|null
     */
    public function resolve(int $customerId) : ?int
    {
        $result = null;

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('customer_entity');

        $query = $connection
            ->select()
            ->from(
                ['main_table' => $tableName],
                ['main_table.group_id']
            )
            ->where('main_table.entity_id = ?', $customerId);
        $groupId = $connection->fetchOne($query);
        if ($groupId) {
            $result = (int) $groupId;
        }

        return $result;
    }
}
