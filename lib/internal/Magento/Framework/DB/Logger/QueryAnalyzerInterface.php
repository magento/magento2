<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Logger;

interface QueryAnalyzerInterface
{
    public const FULL_TABLE_SCAN = 'FULL TABLE SCAN';

    public const NO_INDEX = 'NO INDEX';

    public const FILESORT = 'FILESORT';

    public const DEPENDENT_SUBQUERY = 'DEPENDENT SUBQUERY';

    public const PARTIAL_INDEX = 'PARTIAL INDEX';

    /**
     * Analyze query
     *
     * @param string $sql
     * @param array $bindings
     * @return array
     * @throws QueryAnalyzerException
     */
    public function process(string $sql, array $bindings): array;
}
