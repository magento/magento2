<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

/**
 * DDL triggers is events that can be fired:
 *  - after element creation;
 *  - before/after element modification
 *  - before element removal
 *
 * Trigger is used to make changes in data not in schema, e.g migrate data from column of one table to
 * column of another table.
 *
 * Please note: triggers are used to serve needs of some operations, that can`t be described with declarative schema,
 * for example: renaming. Renaming implemented with removal column with old name and creating with new one.
 * Question with data is solved with help of triggers, that allows to migrate data.
 * This approach is correct from prospective of declaration but is not so fast as ALTER TABLE is.
 * So if you need to perform some renaming operations quickly, please use raw SQL dump instead, that can be taken with
 * help of --dry-run mode
 */
interface DDLTriggerInterface
{
    /**
     * Check whether current trigger can be applied to current statement.
     *
     * @param string $statement
     * @return bool
     */
    public function isApplicable(string $statement) : bool ;

    /**
     * Setup callback to current statement, can generate new statements.
     *
     * @param ElementHistory $elementHistory
     * @return callable
     */
    public function getCallback(ElementHistory $elementHistory) : callable;
}
