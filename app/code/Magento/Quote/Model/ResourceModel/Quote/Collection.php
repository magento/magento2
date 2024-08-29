<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\ResourceModel\Quote;

/**
 * Quotes collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\Quote::class, \Magento\Quote\Model\ResourceModel\Quote::class);
    }

    /**
     * Join table to collection select
     *
     * @param array|string $table
     * @param string $cond
     * @param array|string $cols
     * @return $this
     */
    public function joinLeft(array|string $table, string $cond, array|string $cols = '*')
    {
        if (is_array($table)) {
            $newTable = reset($table);
            $alias = key($table);
            $table = $newTable;
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->joinLeft([$alias => $this->getTable($table)], $cond, $cols);
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }
}
