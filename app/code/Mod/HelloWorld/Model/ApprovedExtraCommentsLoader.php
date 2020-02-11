<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorld\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Approved extra comments loader service.
 */
class ApprovedExtraCommentsLoader
{
    /**
     * @var string
     */
    const QUOTE_TABLE = 'product_extra_comments';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $productSku): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::QUOTE_TABLE);
        $select = $connection
            ->select()
            ->from($tableName, '*')
            ->where('product_sku = ' . "'" . $productSku
                . "'" . ' AND is_approved = 1');
        return $connection->fetchAll($select);
    }
}
