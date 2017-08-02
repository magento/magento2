<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Table;

/**
 * Class Builder
 * @since 2.2.0
 */
class Builder implements BuilderInterface
{
    /**
     * @var \Magento\Framework\DB\Ddl\Table
     * @since 2.2.0
     */
    private $tableInstance;

    /**
     * Builder constructor.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $tableName
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $tableName)
    {
        $this->tableInstance = $connection->newTable($tableName);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function addColumn($name, $type, $size = null, $options = [], $comment = null)
    {
        $this->tableInstance->addColumn($name, $type, $size, $options, $comment);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getTable()
    {
        return $this->tableInstance;
    }
}
