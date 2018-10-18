<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\DB\Select;

/**
 * @api
 * @since 100.1.0
 *
 * @deprecated CatalogInventory will be replaced by Multi-Source Inventory (MSI)
 *             see https://devdocs.magento.com/guides/v2.3/rest/modules/inventory/inventory.html
 */
interface QueryProcessorInterface
{
    /**
     * @param Select $select
     * @param null|array $entityIds
     * @param bool $usePrimaryTable
     * @return Select
     * @since 100.1.0
     */
    public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false);
}
