<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Table;

/**
 * Interface BuilderInterface
 *
 * @api
 */
interface BuilderInterface
{
    /**
     * Adds column to table.
     *
     * $options contains additional options for columns. Supported values are:
     * - 'unsigned', for number types only. Default: FALSE.
     * - 'precision', for numeric and decimal only. Default: taken from $size, if not set there then 0.
     * - 'scale', for numeric and decimal only. Default: taken from $size, if not set there then 10.
     * - 'default'. Default: not set.
     * - 'nullable'. Default: TRUE.
     * - 'primary', add column to primary index. Default: do not add.
     * - 'primary_position', only for column in primary index. Default: count of primary columns + 1.
     * - 'identity' or 'auto_increment'. Default: FALSE.
     *
     * @param string $name the column name
     * @param string $type the column data type
     * @param string|int|array $size the column length
     * @param array $options array of additional options
     * @param string $comment column description
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function addColumn($name, $type, $size = null, $options = [], $comment = null);

    /**
     * @return \Magento\Framework\DB\Ddl\Table
     */
    public function getTable();
}
