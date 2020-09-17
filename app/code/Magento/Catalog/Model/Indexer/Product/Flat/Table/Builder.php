<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Table;

/**
 * Build table structure based on provided columns
 */
class Builder implements BuilderInterface
{
    /**
     * @var \Magento\Framework\DB\Ddl\Table
     */
    private $tableInstance;

    /**
     * Builder constructor.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $tableName
     */
    public function __construct(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $tableName)
    {
        $this->tableInstance = $connection->newTable($tableName);
    }

    /**
     * @inheritdoc
     */
    public function addColumn($name, $type, $size = null, $options = [], $comment = null)
    {
        $this->tableInstance->addColumn($name, $type, $size, $options, $comment);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTable()
    {
        return $this->tableInstance;
    }
}
