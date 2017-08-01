<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Table;

/**
 * Class \Magento\Framework\Indexer\Table\Strategy
 *
 * @since 2.0.0
 */
class Strategy implements StrategyInterface
{
    /**
     * Application resource
     *
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.0.0
     */
    protected $resource;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $useIdxTable = false;

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUseIdxTable()
    {
        return $this->useIdxTable;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setUseIdxTable($value = false)
    {
        $this->useIdxTable = (bool) $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function prepareTableName($tablePrefix)
    {
        return $this->getUseIdxTable()
            ? $tablePrefix . self::IDX_SUFFIX
            : $tablePrefix . self::TMP_SUFFIX;
    }
}
