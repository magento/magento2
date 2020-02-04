<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

/**
 * Statement aggregator for SQL statements for one table.
 * All statements are independent with each other, but neither statement can be included with other in one alter query.
 */
class Statement
{
    /**
     * @var string
     */
    private $statement;

    /**
     * Type can be: ALTER, CREATE or DROP operations.
     * Depends on type different operations will be executed on compilation.
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var callable[]
     */
    private $triggers = [];

    /**
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $tableName
     * @param string $type
     * @param string $statement
     * @param string $resource
     */
    public function __construct(
        string $name,
        string $tableName,
        string $type,
        string $statement,
        string $resource
    ) {
        $this->statement = $statement;
        $this->type = $type;
        $this->tableName = $tableName;
        $this->resource = $resource;
        $this->name = $name;
    }

    /**
     * Get statement.
     *
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * Add trigger to current statement.
     * This means, that statement is final and can`t be modified any more.
     *
     * @param callable $trigger
     */
    public function addTrigger(callable $trigger)
    {
        $this->triggers[] = $trigger;
    }

    /**
     * Get statement type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Get resource name.
     *
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Get triggers array.
     *
     * @return callable[]
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }

    /**
     * Get statement name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
