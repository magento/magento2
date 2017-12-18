<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto\Constraints;

use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraint;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * Reference constraint is type of constraint, where one table from one column
 * is referenced to another table column with the same definition
 */
class Reference extends Constraint implements ElementDiffAwareInterface
{
    /**
     * @var Column
     */
    private $column;

    /**
     * @var Table
     */
    private $referenceTable;

    /**
     * @var Column
     */
    private $referenceColumn;
    /**
     * @var string
     */
    private $onDelete;

    /**
     * @param string $name
     * @param string $elementType
     * @param Table $table
     * @param Column $column
     * @param Table $referenceTable
     * @param Column $referenceColumn
     * @param string $onDelete
     */
    public function __construct(
        string $name,
        string $elementType,
        Table $table,
        Column $column,
        Table $referenceTable,
        Column $referenceColumn,
        string $onDelete
    ) {
        parent::__construct($name, $elementType, $table);
        $this->column = $column;
        $this->referenceTable = $referenceTable;
        $this->referenceColumn = $referenceColumn;
        $this->onDelete = $onDelete;
    }

    /**
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * External column, from table to where we do reference
     *
     * @return Column
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * External table to where we do reference
     *
     *
     * @return Table
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * Trigger param, which attach custom action, on delete value from reference table
     *
     * @return string
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getElementType(),
            'column' => $this->getColumn()->getName(),
            'referenceColumn' => $this->getReferenceColumn()->getName(),
            'referenceTableName' => $this->getReferenceTable()->getName(),
            'tableName' => $this->getTable()->getName(),
            'onDelete' => $this->getOnDelete()
        ];
    }
}
