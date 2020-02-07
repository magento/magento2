<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Model\ResourceModel\Table;

use Magento\Framework\App\ResourceConnection;

/**
 * Provides full list of tables in the database. This list excludes views, to allow different backup process.
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
     * Get list of database tables excluding views.
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->resource->getConnection('backup')->fetchCol(
            "SHOW FULL TABLES WHERE `Table_type` = ?",
            self::TABLE_TYPE
        );
    }
}
