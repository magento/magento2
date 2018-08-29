<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

/**
 * Statement aggregator.
 *
 * Statements are concatenated conditionally, decides which statements go separately and which may be concatenated.
 */
class StatementAggregator
{
    /**
     * Statements batch.
     * Statements that can be merged with each other are ain each bunch.
     *
     * @var array
     */
    private $statementsBank = [];

    /**
     * Verify that statements can be merged.
     *
     * @param Statement $bankStatement
     * @param Statement $statement
     * @return bool
     */
    private function canDoMerge(Statement $bankStatement, Statement $statement)
    {
        /** Modify reference only for 2 different requests */
        if ($statement instanceof ReferenceStatement && $statement->getName() === $bankStatement->getName()) {
            return false;
        }

        /**
         * If we add trigger after some specific statement, than we say that statement is final
         * and can`t be updated anymore. Otherwise trigger can fail.
         *
         * Example: while migrating data from one column to another and another column should be removed,
         * we need to ensure that we create new column, finalize statement, do insert and only after insert
         * do all other statements like DROP old column.
         */
        return empty($bankStatement->getTriggers()) &&
            $statement->getType() === $bankStatement->getType() &&
            $statement->getTableName() === $bankStatement->getTableName() &&
            $statement->getResource() === $bankStatement->getResource();
    }

    /**
     * Add one or few statements and divide them if they can`t be executed in one query.
     *
     * For example, foreign key modification can`t be done in one query.
     * First existing foreign key should be dropped and only then new one can be created.
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
     * Return all statements separated in batches.
     *
     * @return Statement[]
     */
    public function getStatementsBank()
    {
        return $this->statementsBank;
    }
}
