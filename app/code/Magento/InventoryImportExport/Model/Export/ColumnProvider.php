<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Export;

use Magento\Framework\Data\Collection as AttributeCollection;
use Magento\ImportExport\Model\Export;
use \Magento\Framework\Exception\LocalizedException;

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

        if (count($filters[Export::FILTER_ELEMENT_SKIP]) === count($columns)) {
            throw new LocalizedException(__('There is no data for the export.'));
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
