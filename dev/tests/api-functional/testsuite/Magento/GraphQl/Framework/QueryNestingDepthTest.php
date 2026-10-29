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
 * Tests pre-parse nesting depth validation for GraphQL queries.
 *
 * Verifies that deeply nested queries (inline fragments, input objects, list values)
 * are rejected before reaching the recursive parser, preventing stack overflow DoS.
 */
class QueryNestingDepthTest extends GraphQlAbstract
{
    private const ERROR_MESSAGE = 'Query is too deep. Reduce the nesting level of your query.';

    /**
     * Verify a legitimate query with moderate nesting passes validation.
     */
    public function testLegitimateQueryIsAccepted(): void
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
     * Verify deeply nested inline fragments are rejected.
     */
    public function testDeeplyNestedInlineFragmentsAreRejected(): void
    {
        $depth = 250;
        $query = str_repeat('{ ... on Query ', $depth) . '{ __typename }' . str_repeat(' }', $depth);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify deeply nested selection sets are rejected.
     */
    public function testDeeplyNestedSelectionSetsAreRejected(): void
    {
        $depth = 250;
        $query = str_repeat('{ a ', $depth) . '{ __typename }' . str_repeat(' }', $depth);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify deeply nested input objects are rejected.
     */
    public function testDeeplyNestedInputObjectsAreRejected(): void
    {
        $depth = 250;
        $query = '{ products(filter: '
            . str_repeat('{ a: ', $depth)
            . '"x"'
            . str_repeat(' }', $depth)
            . ') { items { name } } }';

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify deeply nested list values are rejected.
     */
    public function testDeeplyNestedListValuesAreRejected(): void
    {
        $depth = 250;
        $query = '{ products(ids: '
            . str_repeat('[', $depth)
            . '1'
            . str_repeat(']', $depth)
            . ') { items { name } } }';

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify a massively nested query (~3000 levels) is rejected without crashing the server.
     */
    public function testMassiveNestingDoesNotCrashServer(): void
    {
        $depth = 3000;
        $query = str_repeat('{ ... on Query ', $depth) . '{ __typename }' . str_repeat(' }', $depth);

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify mixed brace and bracket nesting is counted together.
     */
    public function testMixedBracesAndBracketsNestingIsRejected(): void
    {
        $depth = 125;
        $innerNesting = str_repeat('[', $depth)
            . str_repeat('{ a: ', $depth)
            . '"x"'
            . str_repeat(' }', $depth)
            . str_repeat(']', $depth);
        $query = '{ products(ids: ' . $innerNesting . ') { items { name } } }';

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage(self::ERROR_MESSAGE);
        $this->graphQlMutation($query);
    }

    /**
     * Verify that braces/brackets inside string values are not counted.
     */
    public function testBracesInsideStringsAreIgnored(): void
    {
        $query = <<<QUERY
{
  products(filter: { name: { eq: "{{{{[[[[{{{{" } }) {
    items {
      name
    }
  }
}
QUERY;
        $depthError = false;
        try {
            $this->graphQlQuery($query);
        } catch (ResponseContainsErrorsException $e) {
            $this->assertStringNotContainsString(
                'too deep',
                $e->getMessage(),
                'Braces inside strings should not trigger depth validation'
            );
            $depthError = str_contains($e->getMessage(), 'too deep');
        }
        $this->assertFalse($depthError, 'Braces inside string literals must not be counted toward nesting depth');
    }
}
