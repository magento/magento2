<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema\Db;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * DDL triggers is events that can be fired:
 *  - after element creation;
 *  - before/after element modification
 *  - before element removal
 *
 * Trigger is used to make changes in data not in schema, e.g migrate data from column of one table to
 * column of another table.
 */
interface DDLTriggerInterface
{
    /**
     * Check whether current trigger can be applied to current statement.
     *
     * @param string $statement
     * @return bool
     */
    public function isApplicable($statement);

    /**
     * Setup callback to current statement, can generate new statements.
     *
     * @param ElementInterface $element
     * @return callable
     */
    public function getCallback(ElementInterface $element);
}
