<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Get website id by website code
 */
class GetWebsiteIdByWebsiteCode
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
     * @param string $websiteCode
     * @return int|null
     */
    public function execute(string $websiteCode): ?int
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('store_website');
        $selectQry = $connection->select()->from($tableName, 'website_id')->where('code = ?', $websiteCode);

        $result = $connection->fetchOne($selectQry);
        return (false === $result) ? null : (int)$result;
    }
}
