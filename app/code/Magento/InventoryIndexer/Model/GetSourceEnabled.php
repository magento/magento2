<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Get Enabled field value for Source by Source Code.
 */
class GetSourceEnabled
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get Enabled field value for Source by Source Code.
     * @param string $sourceCode
     * @return int|null
     */
    public function execute(string $sourceCode)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                Source::TABLE_NAME_SOURCE,
                [SourceInterface::ENABLED]
            )
            ->where(SourceInterface::SOURCE_CODE . ' = ?', $sourceCode);
        return $connection->fetchOne($select) ?: null;
    }
}
