<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db\Processors;

/**
 * This class is responsible for read different schema
 * structural elements: indexes, constraints, talbe names and columns
 */
interface DbSchemaCreatorInterface
{
    /**
     * Fragment where primary and unique constraints SQL will be holdeed
     */
    const CONSTRAINT_FRAGMENT = 'constraint';

    /**
     * Fragment where COLUMNS SQL will be holded
     */
    const COLUMN_FRAGMENT = 'column';

    /**
     * Fragment where indexes SQL is holded
     */
    const INDEX_FRAGMENT = 'index';

    /**
     * Create table from SQL fragments, like columns, constraints, foreign keys, indexes, etc
     *
     * @param array $tableOptions
     * @param array $sqlFragments
     * @return void
     */
    public function createTable(array $tableOptions, array $sqlFragments);
}
