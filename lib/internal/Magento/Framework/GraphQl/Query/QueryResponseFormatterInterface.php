<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

interface QueryResponseFormatterInterface
{
    /**
     * Adjust execution result to the format expected by GraphQL response
     *
     * @param array $executionResult
     * @return array
     */
    public function formatResponse(array $executionResult): array;
}
