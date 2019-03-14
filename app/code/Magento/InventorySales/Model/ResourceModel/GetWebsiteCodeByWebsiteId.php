<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Get website code by website id
 */
class GetWebsiteCodeByWebsiteId
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
     * @param int $websiteId
     * @return string|null
     */
    public function execute(int $websiteId): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('store_website');
        $selectQry = $connection->select()->from($tableName, 'code')->where('website_id = ?', $websiteId);

        $result = $connection->fetchOne($selectQry);
        return (false === $result) ? null : $result;
    }
}
