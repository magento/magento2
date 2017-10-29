<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export\Filter;

use Magento\Inventory\Model\ResourceModel\SourceItem\Collection;
use Magento\InventoryImportExport\Model\Export\FilterProcessorInterface;

/**
 * @inheritdoc
 */
class DatetimeFilter implements FilterProcessorInterface
{
    /**
     * @param Collection $collection
     * @param string $columnName
     * @param array|string $value
     * @return void
     */
    public function process(Collection $collection, $columnName, $value)
    {
        if (is_array($value)) {
            $from = $value[0] ?? null;
            $to = $value[1] ?? null;

            if (is_scalar($from) && !empty($from)) {
                $date = (new \DateTime($from))->format('m/d/Y');
                $collection->addFieldToFilter($columnName, ['from' => $date, 'date' => true]);
            }

            if (is_scalar($to) && !empty($to)) {
                $date = (new \DateTime($to))->format('m/d/Y');
                $collection->addFieldToFilter($columnName, ['to' => $date, 'date' => true]);
            }

            return;
        }

        $collection->addFieldToFilter($columnName, ['eq' => $value, 'date' => true]);
    }
}
