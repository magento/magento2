<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Query;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

/**
 * Verify that SearchCriteriaValidator enforces the pageSize limit in GraphQL queries.
 *
 * Regression: Magento_GraphQl module-level di.xml was overriding the global di.xml array argument
 * for CompositeValidator, silently dropping SearchCriteriaValidator from the composite.
 */
class SearchCriteriaValidatorTest extends GraphQlAbstract
{
    /**
     * Verify that a pageSize argument exceeding the configured maximum returns an error.
     */
    #[
        Config('graphql/validation/input_limit_enabled', 1),
        Config('graphql/validation/maximum_page_size', 5)
    ]
    public function testProductsQueryExceedingPageSizeReturnsError()
    {
        $query = <<<QUERY
        {
            products(pageSize: 6, filter: {sku: {eq: "test"}}) {
                total_count
            }
        }
        QUERY;

        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage('Maximum pageSize is 5');
        $this->graphQlQuery($query);
    }

    /**
     * Verify that a pageSize argument within the configured maximum succeeds.
     */
    #[
        Config('graphql/validation/input_limit_enabled', 1),
        Config('graphql/validation/maximum_page_size', 5)
    ]
    public function testProductsQueryWithinPageSizeLimitSucceeds()
    {
        $query = <<<QUERY
        {
            products(pageSize: 5, filter: {sku: {eq: "test"}}) {
                total_count
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
    }

    /**
     * Verify that pageSize is not enforced when input limiting is disabled.
     */
    #[
        Config('graphql/validation/input_limit_enabled', 0),
        Config('graphql/validation/maximum_page_size', 5)
    ]
    public function testProductsQueryPageSizeNotEnforcedWhenLimitingDisabled()
    {
        $query = <<<QUERY
        {
            products(pageSize: 100, filter: {sku: {eq: "test"}}) {
                total_count
            }
        }
        QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
    }
}
