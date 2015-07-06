<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Indexer\Table;

/**
 * Class Strategy
 * @package Magento\Indexer
 */
class Strategy implements StrategyInterface
{
    /**
     * Application resource
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Resource $resource
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource
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
