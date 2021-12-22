<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Get Login as Customer assistance allowed record.
 */
class GetLoginAsCustomerAssistanceAllowed
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
     *  Get Login as Customer assistance allowed record by Customer id.
     *
     * @param int $customerId
     * @return bool
     */
    public function execute(int $customerId): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer_assistance_allowed');

        $select = $connection->select()
            ->from(
                $tableName
            )
            ->where('customer_id = ?', $customerId);

        return !!$connection->fetchOne($select);
    }
}
