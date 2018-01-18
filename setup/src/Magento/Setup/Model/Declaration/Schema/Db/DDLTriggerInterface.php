<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * DDL triggers is events that can be fired:
 *  - after element creation;
 *  - before/after element modification
 *  - before element removal
 *
 * Usually trigger is used to make changes in data not in schema
 * For example, migrate data from column of one table to column of another table
 */
interface DDLTriggerInterface
{
    /**
     * Check whether current trigger can be applied to current statement
     *
     * @param string $statement
     * @return bool
     */
    public function isApplicable($statement);

    /**
     * Setup callback to current statement, can generate new statements
     *
     * @param ElementInterface $element
     * @return Callable
     */
    public function getCallback(ElementInterface $element);
}
