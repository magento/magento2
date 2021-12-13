<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Save Login as Customer assistance allowed record.
 */
class SaveLoginAsCustomerAssistanceAllowed
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
     * Save Login as Customer assistance allowed record by Customer id.
     *
     * @param int $customerId
     * @return void
     */
    public function execute(int $customerId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('login_as_customer_assistance_allowed');

        $connection->insertOnDuplicate(
            $tableName,
            [
                'customer_id' => $customerId
            ]
        );
    }
}
