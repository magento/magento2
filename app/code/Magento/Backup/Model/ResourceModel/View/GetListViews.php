<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backup\Model\ResourceModel\View;

use Magento\Framework\App\ResourceConnection;

/**
 * Get list of database views.
 */
class GetListViews
{
    private const TABLE_TYPE = 'VIEW';

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
     * Get list of database views.
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
