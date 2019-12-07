<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection\Collection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection;

/**
 * An applier of additional filters to a selection collection.
 *
 * The class is introduced to extend filtering abilities of the collection
 * without backward incompatible changes in a corresponding collection class.
 */
class FilterApplier
{
    /**
     * @var array
     */
    private $conditionTypesMap = [
        'eq' => ' = ?',
        'in' => 'IN (?)'
    ];

    /**
     * Applies filter to the given collection in accordance with the given condition.
     *
     * @param Collection $collection
     * @param string $field
     * @param string|array $value
     * @param string $conditionType
     *
     * @return void
     */
    public function apply(Collection $collection, $field, $value, $conditionType = 'eq')
    {
        foreach ($collection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($data['tableName'] == $collection->getTable('catalog_product_bundle_selection')) {
                $field = $tableAlias . '.' . $field;
            }
        }

        $collection->getSelect()
            ->where($field . $this->conditionTypesMap[$conditionType], $value);
    }
}
