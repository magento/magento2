<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model;

use Magento\Framework\GraphQl\Query\QueryResponseFormatterInterface;

class OrderResponseFormatter implements QueryResponseFormatterInterface
{
    private const NODE_IDENTIFIER = 'placeOrder';

    /**
     * @inheritDoc
     */
    public function formatResponse(array $executionResult): array
    {
        if (!$this->isApplicable($executionResult)) {
            return $executionResult;
        }

        $dataErrors = $this->getErrors($executionResult);
        if (empty($dataErrors)) {
            return $executionResult;
        }

        $response = $executionResult['data'][self::NODE_IDENTIFIER] ?: [];
        $response['errors'] = $dataErrors;
        $executionResult['data'][self::NODE_IDENTIFIER] = $response;

        return $executionResult;
    }

    /**
     * Check if the formatter is applicable for the given execution result
     *
     * @param array $executionResult
     * @return bool
     */
    private function isApplicable(array $executionResult): bool
    {
        return isset($executionResult['data']) && key_exists(self::NODE_IDENTIFIER, $executionResult['data']);
    }

    /**
     * Extract errors from the execution result
     *
     * @param array $executionResult
     * @return array
     */
    private function getErrors(array $executionResult): array
    {
        $dataErrors = [];
        if (!empty($executionResult['errors'])) {
            foreach ($executionResult['errors'] as $error) {
                if (isset($error['extensions']['error_code'])) {
                    $dataErrors[] = [
                        'message' => $error['message'],
                        'code' => $error['extensions']['error_code']
                    ];
                }
            }
        }
        return $dataErrors;
    }
}
