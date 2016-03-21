<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup;

/**
 * Data setup cache
 */
interface DataCacheInterface
{
    /**
     * Set data of a row
     *
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @param mixed $value
     * @return void
     */
    public function setRow($table, $parentId, $rowId, $value);

    /**
     * Set data of a field
     *
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function setField($table, $parentId, $rowId, $field, $value);

    /**
     * Gets requested row/field
     *
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @param string|null $field
     * @return mixed Returns false if there is no such record
     */
    public function get($table, $parentId, $rowId, $field = null);

    /**
     * Removed requested row
     *
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @return void
     */
    public function remove($table, $parentId, $rowId);

    /**
     * Checks if requested data exists
     *
     * @param string $table
     * @param string $parentId
     * @param string $rowId
     * @param string|null $field
     * @return bool
     */
    public function has($table, $parentId, $rowId, $field = null);
}
