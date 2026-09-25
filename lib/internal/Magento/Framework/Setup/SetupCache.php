<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup;

/**
 * In-memory cache of DB data for setup operations
 *
 * @api
 */
class SetupCache implements DataCacheInterface
{
    /**
     * Cache storage
     *
     * @var array
     */
    private $data = [];

    /**
     * @inheritdoc
     */
    public function setRow($table, $parentId, $rowId, $value)
    {
        $value = $value !== false ? $value : [];
        $this->data[$table][$parentId][$rowId] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setField($table, $parentId, $rowId, $field, $value)
    {
        $this->data[$table][$parentId][$rowId][$field] = $value;
    }

    /**
     * @inheritdoc
     */
    public function get($table, $parentId, $rowId, $field = null)
    {
        if (null === $field) {
            return $this->data[$table][$parentId][$rowId] ?? false;
        } else {
            return $this->data[$table][$parentId][$rowId][$field] ?? false;
        }
    }

    /**
     * @inheritdoc
     */
    public function remove($table, $parentId, $rowId)
    {
        if (isset($this->data[$table][$parentId][$rowId])) {
            unset($this->data[$table][$parentId][$rowId]);
        }
    }

    /**
     * @inheritdoc
     */
    public function has($table, $parentId, $rowId, $field = null)
    {
        if (null === $field) {
            return !empty($this->data[$table][$parentId][$rowId]);
        } else {
            return !empty($this->data[$table][$parentId][$rowId][$field]);
        }
    }
}
