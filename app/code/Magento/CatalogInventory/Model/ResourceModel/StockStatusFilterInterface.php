<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogInventory\Model\ResourceModel;

use Magento\Framework\DB\Select;

/**
 * In stock status filter interface.
 */
interface StockStatusFilterInterface
{
    public const TABLE_ALIAS = 'stock_status';

    /**
     * Add in-stock status constraint to the select.
     *
     * @param Select $select
     * @param string $productTableAliasAlias
     * @param string $stockStatusTableAlias
     * @param int|null $websiteId
     * @return Select
     */
    public function execute(
        Select $select,
        string $productTableAliasAlias,
        string $stockStatusTableAlias = self::TABLE_ALIAS,
        ?int $websiteId = null
    ): Select;
}
