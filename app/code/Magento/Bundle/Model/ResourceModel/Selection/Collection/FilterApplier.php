<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel\Selection\Collection;

use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Zend_Db_Select_Exception;

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
        'in' => ' IN (?)'
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
     * @throws Zend_Db_Select_Exception
     */
    public function apply(Collection $collection, string $field, $value, string $conditionType = 'eq')
    {
        foreach ($collection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($data['tableName'] == $collection->getTable('catalog_product_bundle_selection')) {
                $field = $tableAlias . '.' . $field;
            }
        }

        $collection->getSelect()->distinct(true)
            ->where($field . $this->conditionTypesMap[$conditionType], $value);
    }
}
