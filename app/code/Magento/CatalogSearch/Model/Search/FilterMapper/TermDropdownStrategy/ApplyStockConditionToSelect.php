<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\CatalogInventory\Model\Stock\Status;

/**
 * Apply stock condition to select.
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
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
            sprintf(
                '%2$s.product_id = %1$s.source_id AND %2$s.stock_status = %3$d',
                $alias,
                $stockAlias,
                Status::STATUS_IN_STOCK
            ),
            []
        );
    }
}
