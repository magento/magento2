<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
     * Storage engine, that silently stores HASH indexes as BTREE.
     */
    private const INNODB_ENGINE = 'innodb';

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
            'indexType' => $this->getComparableIndexType()
        ];
    }

    /**
     * Get index type as it is stored by the table engine.
     *
     * InnoDB accepts HASH indexes but creates them as BTREE, so both declarations are equal for InnoDB tables.
     *
     * @return string
     */
    private function getComparableIndexType(): string
    {
        $indexType = (string)$this->getIndexType();
        if (strtolower($indexType) === 'hash' && $this->table->getEngine() === self::INNODB_ENGINE) {
            return 'btree';
        }

        return $indexType;
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
