<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Setup\Declaration\Schema;

/**
 * Resolver of table names.
 */
class TableNameResolver
{
    /**
     * RegEx pattern is used to search cloned temporary tables used as a replica.
     *
     * @var string
     */
    private $filterPattern = '';

    /**
     * Provides the name of the origin table for cloned tables.
     *
     * Replica tables should be identical to the original -
     * indexes and constraints must use the original table name to calculate their own names.
     *
     * @param string $tableName
     * @return string
     */
    public function getNameOfOriginTable(string $tableName): string
    {
        $tableIsReplica = preg_match($this->getFilterPattern(), $tableName, $matches);

        return $tableIsReplica ? $matches['table_name'] : $tableName;
    }

    /**
     * Provides a RegEx pattern used to search cloned temporary tables used as a replica.
     *
     * @return string
     */
    private function getFilterPattern(): string
    {
        if (!$this->filterPattern) {
            $this->filterPattern = '#(?<table_name>\S+)_replica$#i';
        }

        return $this->filterPattern;
    }
}
