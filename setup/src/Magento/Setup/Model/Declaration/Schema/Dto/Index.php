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
     * @var Table
     */
    private $table;

    /**
     * @var array
     */
    private $columns;

    /**
     * @param string $name
     * @param string $elementType
     * @param Table $table
     * @param array $columns
     */
    public function __construct(
        string $name,
        string $elementType,
        Table $table,
        array $columns
    ) {
        parent::__construct($name, $elementType);
        $this->table = $table;
        $this->columns = $columns;
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
            'type' => $this->getElementType(),
            'columns' => array_map(
                function (Column $column) {
                    return $column->getName();
                },
                $this->getColumns()
            )
        ];
    }
}
