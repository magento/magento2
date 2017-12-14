<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Dto;

use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;

/**
 * Table structural element
 * Aggregate inside itself: columns, constraints and indexes
 * Resource is also specified on this strucural element
 */
class Table extends GenericElement implements ElementInterface
{
    /**
     * @inheritdoc
     */
    protected $structuralElementData;

    /**
     * @var Constraint[]
     */
    private $constraints = [];

    /**
     * @var Column[]
     */
    private $columns = [];

    /**
     * @var string
     */
    protected $elementType = 'table';

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @param array $structuralElementData
     */
    public function __construct(array $structuralElementData)
    {
        $this->structuralElementData = $structuralElementData;
    }

    /**
     * Return different table constraints.
     * It can be constraint like unique key or reference to another table, etc
     *
     * @return Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param string $name
     * @return Constraint | bool
     */
    public function getConstraintByName($name)
    {
        return isset($this->constraints[$name]) ? $this->constraints[$name] : false;
    }

    /**
     * As primary constraint always have one name
     * and can be only one for table
     * it name is allocated into it constraint
     *
     * @return bool|Constraint
     */
    public function getPrimaryConstraint()
    {
        return isset($this->constraints[Internal::PRIMARY_NAME]) ?
            $this->constraints[Internal::PRIMARY_NAME] :
            false;
    }

    /**
     * @param string $name
     * @return Index | bool
     */
    public function getIndexByName($name)
    {
        return isset($this->indexes[$name]) ? $this->indexes[$name] : false;
    }

    /**
     * Return all columns.
     * Note, table always must have columns
     *
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Return all indexes, that are applied to table
     *
     * @return Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * This is workaround, as any DTO object couldnt be changed after instantiation.
     * However there is case, when we have 2 tables with constraints in different tables,
     * that depends to each other table. So we need to setup DTO first and only then pass
     * problematic constraints to it, in order to avoid circular dependency.
     *
     * @param Constraint[] $constraints
     */
    public function addConstraints(array $constraints)
    {
        $this->constraints = array_replace($this->constraints, $constraints);
    }

    /**
     * Add columns
     *
     * @param Column[] $columns
     */
    public function addColumns(array $columns)
    {
        $this->columns = array_replace($this->columns, $columns);
    }

    /**
     * If we want to rename our column: then it name will be changed
     * But we want to search by old name
     * As this name can be used in constraints and indexes
     *
     * @param string $nameOrId
     * @return Column
     */
    public function getColumnByNameOrId($nameOrId)
    {
        if (isset($this->columns[$nameOrId])) {
            return $this->columns[$nameOrId];
        }

        //If it was renamed
        foreach ($this->columns as $column) {
            if ($column->wasRenamedFrom() === $nameOrId) {
                return $column;
            }
        }

        throw new \LogicException(sprintf("Cannot find column with name or id %s", $nameOrId));
    }

    /**
     * Retrieve elements by specific type
     * Allowed types: columns, constraints, indexes...
     *
     * @param string $type
     * @return ElementInterface[] | ElementRenamedInterface[]
     */
    public function getElementsByType($type)
    {
        if (!isset($this->{$type})) {
            throw new \InvalidArgumentException(sprintf("Type %s is not defined", $type));
        }

        return $this->{$type};
    }

    /**
     * This is workaround, as any DTO object couldnt be changed after instantiation.
     * However there is case, when we depends on column definition we need modify our indexes
     *
     * @param array $indexes
     */
    public function addIndexes(array $indexes)
    {
        $this->indexes = array_replace($this->indexes, $indexes);
    }
}
