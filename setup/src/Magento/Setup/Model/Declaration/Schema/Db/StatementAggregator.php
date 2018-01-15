<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

/**
 * Not each statement can be concatanated with others
 * So we need to decide what statement should goes separately and what can be added in single statement
 */
class StatementAggregator
{
    /**
     * Here we have statements batches
     * In each batch we have statements that can be merged with each other
     *
     * @var array
     */
    private $statementsBank = [];


    /**
     * Before we will do merge, we need to ensure that we can do it
     *
     * @param Statement $bankStatement
     * @param Statement $statement
     * @return bool
     */
    private function canDoMerge(Statement $bankStatement, Statement $statement)
    {
        /** We can modify reference only in 2 different requests */
        if ($statement instanceof ReferenceStatement && $statement->getName() === $bankStatement->getName()) {
            return false;
        }

        /**
         * If we add trigger after some specific statement, than we say that statement is final
         * and can`t be updated anymore. Otherwise trigger can fails
         */
        return empty($statement->getTriggers()) &&
            $statement->getType() === $bankStatement->getType() &&
            $statement->getTableName() === $bankStatement->getTableName() &&
            $statement->getResource() === $bankStatement->getResource();
    }

    /**
     * Add one or few statements and divide them if they can`t be executed in one query
     *
     * For example, foreign key modification can`t be done in one query
     * First we need to drop existing foreign key and only then create new one
     *
     * @param Statement[] $statements
     */
    public function addStatements(array $statements)
    {
        foreach ($statements as $statement) {
            /** Go through each bank and see whether statement can be added to it or not */
            foreach ($this->statementsBank as $bankId => $bank) {
                foreach ($bank as $bankStatement) {
                    if (!$this->canDoMerge($bankStatement, $statement)) {
                        continue 2;
                    }
                }

                $this->statementsBank[$bankId][] = $statement;
                continue 2;
            }

            $this->statementsBank[][] = $statement;
        }
    }

    /**
     * Return all statements separated in batches
     *
     * @return Statement[]
     */
    public function getStatementsBank()
    {
        return $this->statementsBank;
    }
}
