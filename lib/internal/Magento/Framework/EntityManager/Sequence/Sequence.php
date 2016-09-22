<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Sequence;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sequence\SequenceInterface;

/**
 * Class Sequence
 */
class Sequence implements SequenceInterface
{
    /**
     * @var string
     */
    protected $connectionName;

    /**
     * @var string
     */
    protected $sequenceTable;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param string $sequenceTable
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
     */
    public function getCurrentValue()
    {
        $select = $this->resource->getConnection($this->connectionName)->select();
        $select->from($this->resource->getTableName($this->sequenceTable));
        return $this->resource->getConnection($this->connectionName)->fetchRow($select);
    }
}
