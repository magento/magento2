<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Setup;

use Magento\Framework\Setup\DataCacheInterface;

/**
 * In-memory cache of DB data
 * @since 2.0.0
 */
class SetupCache implements DataCacheInterface
{
    /**
     * Cache storage
     *
     * @var array
     * @since 2.0.0
     */
    private $data = [];

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRow($table, $parentId, $rowId, $value)
    {
        $this->data[$table][$parentId][$rowId] = $value;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setField($table, $parentId, $rowId, $field, $value)
    {
        $this->data[$table][$parentId][$rowId][$field] = $value;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($table, $parentId, $rowId, $field = null)
    {
        if (null === $field) {
            return isset($this->data[$table][$parentId][$rowId]) ?
                $this->data[$table][$parentId][$rowId] :
                false;
        } else {
            return isset($this->data[$table][$parentId][$rowId][$field]) ?
                $this->data[$table][$parentId][$rowId][$field] :
                false;
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function remove($table, $parentId, $rowId)
    {
        if (isset($this->data[$table][$parentId][$rowId])) {
            unset($this->data[$table][$parentId][$rowId]);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
