<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export\Filter;

use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\InventoryImportExport\Model\Export\FilterProcessorInterface;

/**
 * @inheritdoc
 */
class VarcharFilter implements FilterProcessorInterface
{
    /**
     * @param Collection $collection
     * @param string $columnName
     * @param array|string $value
     * @return void
     */
    public function process(Collection $collection, string $columnName, $value): void
    {
        $collection->addFieldToFilter($columnName, ['like' => '%' . $value . '%']);
    }
}
