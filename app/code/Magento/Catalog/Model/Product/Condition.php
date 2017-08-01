<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * @method string getPkFieldName()
 * @method Condition setPkFieldName(string $fieldName)
 * @method string|array getTable()
 * @method Condition setTable($table)
 * @since 2.0.0
 */
class Condition extends \Magento\Framework\DataObject implements Condition\ConditionInterface
{
    /**
     * @param AbstractCollection $collection
     *
     * @return $this
     * @since 2.0.0
     */
    public function applyToCollection($collection)
    {
        if ($this->getTable() && $this->getPkFieldName()) {
            $collection->joinTable(
                $this->getTable(),
                $this->getPkFieldName() . '=entity_id',
                ['affected_product_id' => $this->getPkFieldName()]
            );
        }
        return $this;
    }

    /**
     * @param AdapterInterface $dbAdapter
     *
     * @return Select|string
     * @since 2.0.0
     */
    public function getIdsSelect($dbAdapter)
    {
        if ($this->getTable() && $this->getPkFieldName()) {
            $select = $dbAdapter->select()->from($this->getTable(), $this->getPkFieldName());
            return $select;
        }
        return '';
    }
}
