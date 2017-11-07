<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\InventoryImportExport\Model\Export\ColumnProviderInterface;
use Magento\ImportExport\Model\Export;

/**
 * @inheritdoc
 */
class ColumnProvider implements ColumnProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getHeaders(AttributeCollection $attributeCollection, array $filters): array
    {
        $columns = [];
        foreach ($attributeCollection->getItems() as $item) {
            $columns[] = $item->getData('id');
        }

        if (!isset($filters[Export::FILTER_ELEMENT_SKIP])) {
            return $columns;
        }

        // remove the skipped from columns
        $skippedAttributes = array_flip($filters[Export::FILTER_ELEMENT_SKIP]);
        foreach ($columns as $key => $value) {
            if (array_key_exists($value, $skippedAttributes) === true) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function getColumns(AttributeCollection $attributeCollection, array $filters): array
    {
        return $this->getHeaders($attributeCollection, $filters);
    }
}
