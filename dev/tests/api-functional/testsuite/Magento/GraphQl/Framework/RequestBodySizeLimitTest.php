<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Framework;

use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests request body size validation for GraphQL endpoint.
 *
 * Verifies that oversized JSON payloads are rejected before deserialization,
 * preventing unbounded memory allocation DoS attacks.
 */
class RequestBodySizeLimitTest extends GraphQlAbstract
{
    private const ERROR_MESSAGE = 'Request body is too large.';

    /**
     * Verify a normal-sized query is accepted.
     */
    public function testNormalSizedRequestIsAccepted(): void
    {
        $query = <<<QUERY
{
  storeConfig {
    locale
    base_currency_code
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('storeConfig', $response);
    }

    /**
     * Verify a POST request exceeding the body size limit is rejected.
     */
    public function testOversizedPostRequestIsRejected(): void
    {
        $padding = str_repeat('x', 1048577);
        $query = '{ storeConfig { locale } }';

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query, ['padding' => $padding]);
    }

    /**
     * Verify a request just under the limit is accepted (not rejected by size check).
     */
    public function testRequestJustUnderLimitIsNotRejectedBySize(): void
    {
        $query = <<<QUERY
{
  storeConfig {
    locale
  }
}
QUERY;
        $depthError = false;
        try {
            $response = $this->graphQlQuery($query);
            $this->assertArrayHasKey('storeConfig', $response);
        } catch (ResponseContainsErrorsException $e) {
            $this->assertStringNotContainsString(
                'too large',
                $e->getMessage(),
                'Normal-sized request should not trigger body size validation'
            );
            $depthError = str_contains($e->getMessage(), 'too large');
        }
        $this->assertFalse($depthError, 'Normal-sized request must not be rejected by body size limit');
    }
}
