<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Table;

/**
 * Class Strategy
 * @package Magento\Indexer
 */
class Strategy implements StrategyInterface
{
    /**
     * Application resource
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Use index table directly
     *
     * @var bool
     */
    protected $useIdxTable = false;

    /**
     * {@inheritdoc}
     */
    public function getUseIdxTable()
    {
        return $this->useIdxTable;
    }

    /**
     * {@inheritdoc}
     */
    public function setUseIdxTable($value = false)
    {
        $this->useIdxTable = (bool) $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($tablePrefix)
    {
        return $this->resource->getTableName($this->prepareTableName($tablePrefix));
    }

    /**
     * Prepare index table name
     *
     * @param string $tablePrefix
     *
     * @return string
     */
    public function prepareTableName($tablePrefix)
    {
        return $this->getUseIdxTable()
            ? $tablePrefix . self::IDX_SUFFIX
            : $tablePrefix . self::TMP_SUFFIX;
    }
}
