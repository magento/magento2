<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Query;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

/**
 * Test to validate input of Graphql requests
 */
class QueryComplexityLimiterTest extends GraphQlAbstract
{
    /**
     * Test for validating query within alias limit
     */
    #[
        Config('graphql/validation/alias_limit_enabled', 1),
        Config('graphql/validation/maximum_alias_allowed', 3)
    ]
    public function testQueryWithinAliasLimit()
    {
        $query = <<<QUERY
        {
            productOne: products(filter: {sku: {eq: "1"}}) {
                items {
                    id
                    name
                }
            }
            productTwo: products(filter: {sku: {eq: "2"}}) {
                items {
                    id
                    name
                }
            }
            productThree: products(filter: {sku: {eq: "3"}}) {
                items {
                    id
                    name
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('productOne', $response);
        $this->assertArrayHasKey('productTwo', $response);
        $this->assertArrayHasKey('productThree', $response);
    }

    /**
     * Test for validating query exceeding complexity limit
     */
    #[
        Config('graphql/validation/alias_limit_enabled', 1),
        Config('graphql/validation/maximum_alias_allowed', 2)
    ]
    public function testQueryExeedingAliasLimit()
    {
        $query = <<<QUERY
        {
             productOne: products(filter: {sku: {eq: "1"}}) {
                items {
                    id
                    name
                }
            }
            productTwo: products(filter: {sku: {eq: "2"}}) {
                items {
                    id
                    name
                }
            }
            productThree: products(filter: {sku: {eq: "3"}}) {
                items {
                    id
                    name
                }
            }
        }
        QUERY;

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Max Aliases in query should be 2 but got 3.');
        $this->graphQlQuery($query);
    }

    /**
     * Test for validating query with alias limit disabled
     */
    #[
        Config('graphql/validation/alias_limit_enabled', 0),
        Config('graphql/validation/maximum_alias_allowed', 1)
    ]
    public function testQueryWithinAliasLimitDisabled()
    {
        
        $query = <<<QUERY
        {
            productOne: products(filter: {sku: {eq: "1"}}) {
                items {
                    id
                    name
                }
            }
            productTwo: products(filter: {sku: {eq: "2"}}) {
                items {
                    id
                    name
                }
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('productOne', $response);
        $this->assertArrayHasKey('productTwo', $response);
    }
}
