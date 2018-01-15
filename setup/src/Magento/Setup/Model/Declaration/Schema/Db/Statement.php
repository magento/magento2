<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

/**
 * Statement object aggregates different SQL statements and run all of them for one table
 * All statements are independent with each other, but not each statement can be included with each other
 * in one alter query, for example
 */
class Statement
{
    /**
     * @var string
     */
    private $statement;

    /**
     * Type can be: ALTER, CREATE or DROP operations
     * Depends on type different operations will be executed on compilation
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
     * @var Callable[]
     */
    private $triggers = [];

    /**
     * @var string
     */
    private $name;

    /**
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
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    /**
     * Add trigger to current statement
     * This means, that statement is final and can`t be modified any more
     *
     * @param callable $trigger
     */
    public function addTrigger(Callable $trigger)
    {
        $this->triggers[] = $trigger;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return Callable[]
     */
    public function getTriggers(): array
    {
        return $this->triggers;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
