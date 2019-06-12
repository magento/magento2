<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Setup\Declaration\Schema\Dto\Constraints;

use Magento\Framework\Setup\Declaration\Schema\Dto\Column;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraint;
use Magento\Framework\Setup\Declaration\Schema\Dto\ElementDiffAwareInterface;
use Magento\Framework\Setup\Declaration\Schema\Dto\Table;

/**
 * Reference (Foreign Key) constraint.
 */
class Reference extends Constraint implements ElementDiffAwareInterface
{
    /**
     * In case if we will need to change this object: add, modify or drop, we will need
     * to define it by its type.
     */
    const TYPE = 'reference';

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
     * Constructor.
     *
     * @param string $name
     * @param string $type
     * @param Table $table
     * @param string $nameWithoutPrefix
     * @param Column $column
     * @param Table $referenceTable
     * @param Column $referenceColumn
     * @param string $onDelete
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        string $name,
        string $type,
        Table $table,
        string $nameWithoutPrefix,
        Column $column,
        Table $referenceTable,
        Column $referenceColumn,
        string $onDelete
    ) {
        parent::__construct($name, $type, $table, $nameWithoutPrefix);
        $this->column = $column;
        $this->referenceTable = $referenceTable;
        $this->referenceColumn = $referenceColumn;
        $this->onDelete = $onDelete;
    }

    /**
     * Get column instance.
     *
     * @return Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Get external column in reference table.
     *
     * @return Column
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * Get external referenced table.
     *
     * @return Table
     */
    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    /**
     * On delete action.
     *
     * @return string
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * For foreign key type is always 'reference'.
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'type' => $this->getType(),
            'column' => $this->getColumn()->getName(),
            'referenceColumn' => $this->getReferenceColumn()->getName(),
            'referenceTableName' => $this->getReferenceTable()->getName(),
            'tableName' => $this->getTable()->getName(),
            'onDelete' => $this->getOnDelete(),
            'resource' => $this->getTable()->getResource()
        ];
    }
}
