<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Model\ResourceModel\Table;

use Magento\Framework\App\ResourceConnection;

/**
 * Class GetListTables
 */
class GetListTables
{
    private const TABLE_TYPE = 'BASE TABLE';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get base tables
     *
     * @return array
     */
    public function execute()
    {
        return $this->resource->getConnection('backup')->fetchCol(
            "SHOW FULL TABLES WHERE `Table_type` = ?",
            self::TABLE_TYPE
        );
    }
}
