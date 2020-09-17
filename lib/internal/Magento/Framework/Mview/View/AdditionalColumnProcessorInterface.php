<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View;

use Magento\Framework\DB\Ddl\Table;

interface AdditionalColumnProcessorInterface
{
    /**
     * Return triggers columns that should participate in trigger creation
     *
     * @param string $eventPrefix
     * @param array $additionalColumns
     * @return array
     */
    public function getTriggerColumns(string $eventPrefix, array $additionalColumns): array ;

    /**
     * Process column for DDL table
     *
     * @param Table $table
     * @param string $columnName
     * @return void
     */
    public function processColumnForCLTable(Table $table, string $columnName): void ;

    /**
     * Retrieve pre-statement for trigger
     * For instance DQL
     *
     * @return string
     */
    public function getPreStatements(): string;
}
