<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class MaxHeapTableSizeProcessor
{
    /**
     * Database connection adapter
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected $connection;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $defaultMaxHeapTableSie;

    /**
     * Current max_heap_table_size value (in Bytes)
     *
     * @var int
     * @since 2.0.0
     */
    protected $currentMaxHeapTableSize = null;

    /**
     * @param ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
        $this->defaultMaxHeapTableSie = 1024 * 1024 * 64;
    }

    /**
     * Set max_heap_table_size value in Bytes. By default value is 64M
     *
     * @param int|null $maxHeapTableSize
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @return void
     * @since 2.0.0
     */
    public function set($maxHeapTableSize = null)
    {
        $maxHeapTableSize = (int) (null === $maxHeapTableSize ? $this->defaultMaxHeapTableSie : $maxHeapTableSize);
        if (!$maxHeapTableSize) {
            throw new \InvalidArgumentException('Wrong max_heap_table_size parameter');
        }

        $this->currentMaxHeapTableSize = (int)$this->connection->fetchOne('SELECT @@session.max_heap_table_size');
        if (!$this->currentMaxHeapTableSize) {
            throw new \RuntimeException('Can not extract max_heap_table_size');
        }

        $this->connection->query('SET SESSION max_heap_table_size = ' . $maxHeapTableSize);
    }

    /**
     * Restore max_heap_table_size value
     *
     * @throws \RuntimeException
     * @return void
     * @since 2.0.0
     */
    public function restore()
    {
        if (null === $this->currentMaxHeapTableSize) {
            throw new \RuntimeException('max_heap_table_size parameter is not set');
        }
        $this->connection->query('SET SESSION max_heap_table_size = ' . $this->currentMaxHeapTableSize);
    }
}
