<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Sequence;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class Sequence
 * @since 2.1.0
 */
class Sequence implements SequenceInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $connectionName;

    /**
     * @var string
     * @since 2.1.0
     */
    protected $sequenceTable;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.1.0
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $sequenceTable
     * @since 2.1.0
     */
    public function __construct(
        ResourceConnection $resource,
        $connectionName,
        $sequenceTable
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->sequenceTable = $sequenceTable;
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function getNextValue()
    {
        $this->resource->getConnection($this->connectionName)
            ->insert($this->resource->getTableName($this->sequenceTable), []);
        return $this->resource->getConnection($this->connectionName)
            ->lastInsertId($this->resource->getTableName($this->sequenceTable));
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function getCurrentValue()
    {
        $select = $this->resource->getConnection($this->connectionName)->select();
        $select->from($this->resource->getTableName($this->sequenceTable));
        return $this->resource->getConnection($this->connectionName)->fetchRow($select);
    }
}
