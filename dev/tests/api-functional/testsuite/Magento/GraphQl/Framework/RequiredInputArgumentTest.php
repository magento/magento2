<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Framework;

use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;

/**
 * Test that required input parameters are properly validated on framework level
 */
class RequiredInputArgumentTest extends GraphQlAbstract
{

    /**
     * Test that a simple input value will be treated as required
     *
     * We should see error message from framework not the Resolver
     * urlResolver query has required input arg "url"
     */
    public function testSimpleInputArgumentRequired()
    {
        $query = <<<QUERY
    {
        testQueryWithTopLevelMandatoryInputArguments{
            item_id
            name
        }
    }
QUERY;

        $expectedExceptionsMessage = 'GraphQL response contains errors:'
            . ' Field "testQueryWithTopLevelMandatoryInputArguments" argument "topLevelArgument"'
            . ' of type "String!" is required but not provided.';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);

        $this->graphQlQuery($query);
    }

    /**
     * Test that a more complex required argument is handled properly
     *
     * testInputQueryWithMandatoryArguments mutation has required parameter input.query_items.query_item_id
     */
    public function testInputObjectArgumentRequired()
    {
        $query = <<<QUERY
    query {
        testQueryWithNestedMandatoryInputArguments(
            input: {
                query_id: "foobar"
                query_items: [
                    {
                        quantity: 2
                    }
                ]
            }
        ) {
            item_id
            name
        }
    }
QUERY;

        $expectedExceptionsMessage = 'GraphQL response contains errors:'
            . ' Field QueryWithMandatoryArgumentsInput.query_item_id of required type Int! was not provided.';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);

        $this->graphQlMutation($query);
    }
}
