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
        urlResolver{
            id
            type
        }
    }
QUERY;

        $expectedExceptionsMessage = 'GraphQL response contains errors:'
            . ' Field "urlResolver" argument "url" of type "String!" is required but not provided.';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);

        $this->graphQlQuery($query);
    }

    /**
     * Test that a more complex required argument is handled properly
     *
     * updateCartItems mutation has required parameter input.cart_items.cart_item_id
     */
    public function testInputObjectArgumentRequired()
    {
        $query = <<<QUERY
    mutation {
        updateCartItems(
            input: {
                cart_id: "foobar"
                cart_items: [
                    {
                        quantity: 2
                    }
                ]
            }
        ) {
            cart {
                total_quantity
            }
        }
    }
QUERY;

        $expectedExceptionsMessage = 'GraphQL response contains errors:'
            . ' Field CartItemUpdateInput.cart_item_id of required type Int! was not provided.';
        $this->expectException(ResponseContainsErrorsException::class);
        $this->expectExceptionMessage($expectedExceptionsMessage);

        $this->graphQlMutation($query);
    }
}
