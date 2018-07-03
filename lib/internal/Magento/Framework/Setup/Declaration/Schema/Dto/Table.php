<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Dto;

use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Framework\Setup\Declaration\Schema\Dto\Constraints\Reference;

/**
 * Table structural element
 * Aggregate inside itself: columns, constraints and indexes
 * Resource is also specified on this strucural element
 */
class Table extends GenericElement implements
    ElementInterface,
    ElementDiffAwareInterface
{
    /**
     * In case if we will need to change this object: add, modify or drop, we will need
     * to define it by its type
     */
    const TYPE = 'table';

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
    protected $type = 'table';

    /**
     * @var Index[]
     */
    private $indexes = [];

    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $engine;

    /**
     * @var string
     */
    private $nameWithoutPrefix;

    /**
     * @var null|string
     */
    private $comment;

    /**
     * @var string
     */
    private $onCreate;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $collation;

    /**
     * @param string $name
     * @param string $type
     * @param string $nameWithoutPrefix
     * @param string $resource
     * @param string $engine
     * @param string $charset
     * @param string $collation
     * @param string|null $comment
     * @param array $columns
     * @param array $indexes
     * @param array $constraints
     * @param string $onCreate
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type,
        string $nameWithoutPrefix,
        string $resource,
        string $engine,
        string $charset,
        string $collation,
        string $onCreate,
        string $comment = null,
        array $columns = [],
        array $indexes = [],
        array $constraints = []
    ) {
        parent::__construct($name, $type);
        $this->columns = $columns;
        $this->indexes = $indexes;
        $this->constraints = $constraints;
        $this->resource = $resource;
        $this->engine = $engine;
        $this->nameWithoutPrefix = $nameWithoutPrefix;
        $this->comment = $comment;
        $this->onCreate = $onCreate;
        $this->charset = $charset;
        $this->collation = $collation;
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
     * This method lookup only for foreign keys constraints
     *
     * @return Reference[]
     */
    public function getReferenceConstraints()
    {
        $constraints = [];

        foreach ($this->getConstraints() as $constraint) {
            if ($constraint instanceof Reference) {
                $constraints[$constraint->getName()] = $constraint;
            }
        }

        return $constraints;
    }

    /**
     * As primary constraint always have one name
     * and can be only one for table
     * it name is allocated into it constraint
     *
     * @return bool|Internal
     */
    public function getPrimaryConstraint()
    {
        return isset($this->constraints[Internal::PRIMARY_NAME]) ?
            $this->constraints[Internal::PRIMARY_NAME] :
            false;
    }

    /**
     * Retrieve internal constraints
     *
     * @return array
     */
    public function getInternalConstraints() : array
    {
        $constraints = [];
        foreach ($this->getConstraints() as $constraint) {
            if ($constraint instanceof Internal) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
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
     * Retrieve shard name, on which table will exists
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
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
     * Retrieve information about trigger
     *
     * @return string
     */
    public function getOnCreate()
    {
        return $this->onCreate;
    }

    /**
     * If column exists - retrieve column
     *
     * @param  string $nameOrId
     * @return Column | bool
     */
    public function getColumnByName($nameOrId)
    {
        if (isset($this->columns[$nameOrId])) {
            return $this->columns[$nameOrId];
        }

        return false;
    }

    /**
     * Retrieve elements by specific type
     * Allowed types: columns, constraints, indexes...
     *
     * @param  string $type
     * @return ElementInterface[]
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
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @inheritdoc
     */
    public function getDiffSensitiveParams()
    {
        return [
            'resource' => $this->getResource(),
            'engine' => $this->getEngine(),
            'comment' => $this->getComment(),
            'charset' => $this->getCharset(),
            'collation' => $this->getCollation()
        ];
    }

    /**
     * Return charset of table
     *
     * @return string
     */
    public function getCharset() : string
    {
        return $this->charset;
    }

    /**
     * Return charset of table
     *
     * @return string
     */
    public function getCollation() : string
    {
        return $this->collation;
    }

    /**
     * @return string
     */
    public function getNameWithoutPrefix(): string
    {
        return $this->nameWithoutPrefix;
    }

    /**
     * @return null|string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
