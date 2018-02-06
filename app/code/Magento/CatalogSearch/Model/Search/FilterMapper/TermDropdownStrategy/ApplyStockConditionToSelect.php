<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Apply stock condition to select.
 */
class ApplyStockConditionToSelect
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
     * @param string $alias
     * @param string $stockAlias
     * @param Select $select
     *
     * @return void
     */
    public function execute(
        string $alias,
        string $stockAlias,
        Select $select
    ) {
        $select->joinInner(
            [$stockAlias => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
            sprintf('%2$s.product_id = %1$s.source_id', $alias, $stockAlias),
            []
        );
    }
}
