<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\TestModule;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for basic GraphQl features
 */
class GraphQlQueryTest extends GraphQlAbstract
{
    public function testQueryTestModuleReturnsResults()
    {
        $id = 1;

        $query = <<<QUERY
{
    testItem(id: {$id})
    {
        item_id
        name
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('testItem', $response);
        $testItem = $response['testItem'];
        $this->assertArrayHasKey('item_id', $testItem);
        $this->assertArrayHasKey('name', $testItem);
        $this->assertEquals(1, $testItem['item_id']);
        $this->assertEquals('itemName', $testItem['name']);
    }

    public function testQueryTestModuleExtensionAttribute()
    {
        $id = 2;

        $query = <<<QUERY
{
    testItem(id: {$id})
    {
        item_id
        name
        integer_list
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('testItem', $response);
        $testItem = $response['testItem'];
        $this->assertArrayHasKey('integer_list', $testItem);
        $this->assertEquals([3, 4, 5], $testItem['integer_list']);
    }

    public function testQueryViaGetRequestReturnsResults()
    {
        $id = 1;

        $query = <<<QUERY
{
    testItem(id: {$id})
    {
        item_id
        name
    }
}
QUERY;

        $response = $this->graphQlQuery($query, [], '', []);

        $this->assertArrayHasKey('testItem', $response);
    }

    public function testQueryViaGetRequestWithVariablesReturnsResults()
    {
        $id = 1;

        $query = <<<QUERY
query getTestItem(\$id: Int!)
{
    testItem(id: \$id)
    {
        item_id
        name
    }
}
QUERY;
        $variables = [
            "id" => $id
        ];

        $response = $this->graphQlQuery($query, $variables, '', []);

        $this->assertArrayHasKey('testItem', $response);
    }

    public function testQueryTestUnionResults()
    {
        $query = <<<QUERY
{
    testUnion {
      __typename
      ... on TypeCustom1 {
          custom_name1
      }
      ... on TypeCustom2 {
          custom_name2
      }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('testUnion', $response);
        $testUnion = $response['testUnion'];
        $this->assertArrayHasKey('custom_name1', $testUnion);
        $this->assertEquals('custom_name1_value', $testUnion['custom_name1']);
        $this->assertArrayNotHasKey('custom_name2', $testUnion);
    }
}
