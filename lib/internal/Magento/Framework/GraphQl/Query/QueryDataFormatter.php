<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

class QueryDataFormatter
{
    /**
     * @var array
     */
    private array $formatterPool = [];

    /**
     * @param QueryResponseFormatterInterface[] $formatters
     */
    public function __construct(array $formatters = [])
    {
        foreach ($formatters as $formatter) {
            if ($formatter instanceof QueryResponseFormatterInterface) {
                $this->formatterPool[] = $formatter;
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Formatter must implement %s', QueryResponseFormatterInterface::class)
                );
            }
        }
    }
    /**
     * Format the response using registered formatters
     *
     * @param array $executionResult
     * @return array
     */
    public function formatResponse(array $executionResult): array
    {
        foreach ($this->formatterPool as $formatter) {
            $executionResult = $formatter->formatResponse($executionResult);
        }
        return $executionResult;
    }
}
