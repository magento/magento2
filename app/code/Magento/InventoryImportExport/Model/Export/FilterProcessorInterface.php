<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;

/**
 * @api
 */
interface FilterProcessorInterface
{
    /**
     * @param Collection $collection
     * @param string $columnName
     * @param array|string $value
     * @return void
     */
    public function process(Collection $collection, $columnName, $value);
}
