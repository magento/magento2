<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto;

/**
 * Index structural element.
 * Used to speedup read operations from SQL database.
 */
class Index extends GenericElement implements
    ElementInterface,
    TableElementInterface,
    ElementDiffAwareInterface
{
    /**
     * Element type.
     */
    const TYPE = 'index';

    /**
     * Fulltext index type.
     */
    const FULLTEXT_INDEX = "fulltext";

    /**
     * @var Table
     */
    private $table;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var string
     */
    private $indexType;

    /**
     * @var string
     */
    private $nameWithoutPrefix;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param array $columns
     * @param string $indexType
     * @param string $nameWithoutPrefix
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        array $columns,
        string $indexType,
        string $nameWithoutPrefix
    ) {
        parent::__construct($name, $type);
        $this->table = $table;
        $this->columns = $columns;
        $this->indexType = $indexType;
        $this->nameWithoutPrefix = $nameWithoutPrefix;
    }

    /**
     * Return columns in order, in which they should go in composite index.
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'columns' => $this->getColumnNames(),
            'indexType' => $this->getIndexType()
        ];
    }

    /**
     * Retrieve array with column names from column objects collections.
     *
     * @return array
     */
    public function getColumnNames()
    {
        return array_map(
            function (Column $column) {
                return $column->getName();
            },
            $this->getColumns()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getElementType()
    {
        return self::TYPE;
    }

    /**
     * Get index type (FULLTEXT, BTREE, HASH).
     *
     * @return string
     */
    public function getIndexType()
    {
        return $this->indexType;
    }

    /**
     * Retrieve the index name which is calculated without table prefix.
     *
     * @return string
     */
    public function getNameWithoutPrefix()
    {
        return $this->nameWithoutPrefix;
    }
}
