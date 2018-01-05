<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

/**
 * Statement object aggregates different SQL statements and run all of them for one table
 */
class Statement
{
    /**
     * @var array
     */
    private $statements;

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
     * @param string $tableName
     * @param string $type
     * @param string $statement
     * @param string $resource
     */
    public function __construct(
        string $tableName,
        string $type,
        string $statement,
        string $resource
    ) {
        $this->statements[] = $statement;
        $this->type = $type;
        $this->tableName = $tableName;
        $this->resource = $resource;
    }

    /**
     * Can merge few different statements with each other
     *
     * @param Statement $statement
     * @return $this
     */
    public function merge(Statement $statement)
    {
        $this->statements = array_merge(
            $this->getStatements(),
            $statement->getStatements()
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getStatements(): array
    {
        return $this->statements;
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
     * Before we will do merge, we need to ensure that we can do it
     *
     * @param Statement $statement
     * @return bool
     */
    public function canDoMerge(Statement $statement)
    {
        if (!empty($this->triggers)) {
            /**
             * If we add trigger after some specific statement, than we say that statement is final
             * and can`t be updated anymore. Otherwise trigger can fails
             */
            return false;
        }

        return $statement->getType() === $this->getType() &&
            $statement->getTableName() === $this->getTableName() &&
            $statement->getResource() === $this->getResource();
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
}
