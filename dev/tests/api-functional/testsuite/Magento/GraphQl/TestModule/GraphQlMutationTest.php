<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\TestModule;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Make sure that it is possible to use GraphQL mutations in Magento
 */
class GraphQlMutationTest extends GraphQlAbstract
{
    public function testMutation()
    {
        $id = 3;

        $query = <<<MUTATION
mutation {
  testItem(id: {$id}) {
    item_id,
    name,
    integer_list
  }
}
MUTATION;

        $response = $this->graphQlMutation($query);
        $this->assertArrayHasKey('testItem', $response);
        $testItem = $response['testItem'];
        $this->assertArrayHasKey('integer_list', $testItem);
        $this->assertEquals([4, 5, 6], $testItem['integer_list']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Mutation requests allowed only for POST requests
     */
    public function testMutationIsNotAllowedViaGetRequest()
    {
        $id = 3;

        $query = <<<MUTATION
mutation {
  testItem(id: {$id}) {
    item_id,
    name,
    integer_list
  }
}
MUTATION;

        $this->graphQlQuery($query, [], '', []);
    }
}
