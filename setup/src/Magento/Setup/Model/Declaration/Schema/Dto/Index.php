<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * Index structural element
 * Used to speedup read operations from SQL database
 */
class Index extends GenericElement implements
    ElementInterface,
    TableElementInterface,
    ElementDiffAwareInterface
{
    /**
     * In case if we will need to change this object: add, modify or drop, we will need
     * to define it by its type
     */
    const TYPE = 'index';

    /**
     * Fulltext index type
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
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param array $columns
     * @param string $indexType
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        array $columns,
        string $indexType
    ) {
        parent::__construct($name, $type);
        $this->table = $table;
        $this->columns = $columns;
        $this->indexType = $indexType;
    }

    /**
     * Return columns in order, in which they should go in composite index
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Retrieve table name
     *
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @inheritdoc
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
     * Retrieve array with column names from column objects collections
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
     * @inheritdoc
     */
    public function getElementType()
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getIndexType()
    {
        return $this->indexType;
    }
}
