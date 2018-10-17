<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;

/**
 * @api
 */
interface FilterProcessorInterface
{
    /**
     * Filter Processor Interface is used as an Extension Point for each Attribute Data Type (Backend Type)
     * to process filtering applied from Export Grid UI
     * to all attributes of Entity being exported
     *
     * @param Collection $collection
     * @param string $columnName
     * @param array|string $value
     * @return void
     */
    public function process(Collection $collection, string $columnName, $value): void;
}
