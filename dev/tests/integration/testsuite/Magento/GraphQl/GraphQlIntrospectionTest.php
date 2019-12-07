<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\Framework\GraphQl\Schema\Type\InputObjectType;
use Magento\Framework\GraphQl\Schema\Type\ObjectType;
use Magento\Framework\ObjectManagerInterface;

class GraphQlIntrospectionTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\GraphQl\SchemaFactory */
    private $schemaFactory;

    /** @var  ObjectManagerInterface */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->schemaFactory = $this->objectManager->get(\Magento\Framework\GraphQl\SchemaFactory::class);
    }

    public function testIntrospectionQuery()
    {
        $emptySchema = $this->schemaFactory->create(
            [
                'query' => new ObjectType(
                    [
                        'name' => 'Query',
                        'description' =>'Description at type level',
                        'fields' => ['a' => \GraphQL\Type\Definition\Type::string()]
                    ]
                )
            ]
        );
        $request =
            <<<QUERY
query IntrospectionQuery {
__schema {
queryType { name }
types{
...FullType
}
}
}
fragment FullType on __Type{
name
description
kind
fields(includeDeprecated:true){
name
args{
...InputValue
}
 }
}

fragment TypeRef on __Type {
kind
name
ofType{
kind
name
}
}
fragment InputValue on __InputValue {
name
description
type { ...TypeRef }
defaultValue
}
QUERY;
        $response = \GraphQL\GraphQL::executeQuery($emptySchema, $request);
        $output = $response->toArray()['data']['__schema'];
        $this->assertEquals('Query', $output['queryType']['name']);
        $this->assertEquals($output['types'][0]['kind'], 'OBJECT');
        $expectedFragment =
            [
                'name' => 'Query',
                'description' => 'Description at type level',
                'kind' => 'OBJECT',
                'fields' => [
                    [
                        'name' => 'a',
                        'args' => []
                    ]
                ]
            ];
        $this->assertContains($expectedFragment, $output['types']);
    }

    /**
     * Tests an InputObjectType with NON Null field and description at Field level
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectsInputObjectWithNonNullInputField()
    {
        $testInputObject = new InputObjectType(
            [
                'name' => 'ProductFilterInput',
                'fields' => [
                    'attributeA' => [
                        'type' => \GraphQL\Type\Definition\Type::nonNull(
                            \GraphQL\Type\Definition\Type::string()
                        ),
                        'description' => 'testDescriptionForA'
                    ],
                    'attributeB' => [
                        'type' => \GraphQL\Type\Definition\Type::listOf(
                            \GraphQL\Type\Definition\Type::string()
                        )
                    ],
                    'attributeC' => ['type' => \GraphQL\Type\Definition\Type::string(), 'defaultValue' => null],
                    'attributeD' => [
                        'type' => \GraphQL\Type\Definition\Type::string(),
                        'defaultValue' => 'test',
                        'description' => 'testDescriptionForD'
                    ],
                ]
            ]
        );
        $TestType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'field' => [
                    'type' => \GraphQL\Type\Definition\Type::string(),
                    'args' => ['complex' => ['type' => $testInputObject]],
                    'resolve' => function ($args) {
                        return json_encode($args['complex']);
                    }
                ]
            ]
        ]);
        $testSchema = $this->schemaFactory->create(
            ['query' => $TestType]
        );

        $request =
            <<<QUERY
{
        __schema {
          types {
            kind
            name
            inputFields {
              name
              description
              type { ...TypeRef }
              defaultValue
            }
          }
        }
      }
      fragment TypeRef on __Type {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
            }
          }
        }
}
QUERY;
        $response = \GraphQL\GraphQL::executeQuery($testSchema, $request);
        $expectedResult =
            [
                'kind'=> 'INPUT_OBJECT',
                'name'=> 'ProductFilterInput',
                'inputFields'=> [
                    [
                        'name'=> 'attributeA',
                        'description'=> 'testDescriptionForA',
                        'type'=> [
                            'kind'=> 'NON_NULL',
                            'name'=> null,
                            'ofType'=> [
                                'kind'=> 'SCALAR',
                                'name'=> 'String',
                                'ofType'=> null
                            ]
                        ],
                        'defaultValue'=> null
                    ],
                    [
                        'name'=> 'attributeB',
                        'description'=> null,
                        'type'=> [
                            'kind'=> 'LIST',
                            'name'=> null,
                            'ofType'=> [
                                'kind'=> 'SCALAR',
                                'name'=> 'String',
                                'ofType'=> null
                            ]
                        ],
                        'defaultValue'=> null
                    ],
                    [
                        'name'=> 'attributeC',
                        'description'=> null,
                        'type'=> [
                            'kind'=> 'SCALAR',
                            'name'=> 'String',
                            'ofType'=> null
                        ],
                        'defaultValue'=> 'null'
                    ],
                    [
                        'name'=> 'attributeD',
                        'description'=> 'testDescriptionForD',
                        'type'=> [
                            'kind'=> 'SCALAR',
                            'name'=> 'String',
                            'ofType'=> null
                        ],
                        'defaultValue'=> '"test"'
                    ]
                ]
            ];
        $output = $response->toArray()['data']['__schema']['types'];
        $this->assertContains($expectedResult, $output);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectsIncludeTheDeprecatedParameter()
    {
        $testSchema = $this->schemaFactory->create(
            [
                'query' => new ObjectType(
                    [
                    'name' => 'Query',
                    'fields' => [
                       'deprecated' => [
                         'type' => \GraphQL\Type\Definition\Type::string(),
                         'deprecationReason' =>'Deprecated in an older version'
                       ],
                         'nonDeprecated' => [
                            'type' => \GraphQL\Type\Definition\Type::string()
                         ]
                    ]
                    ]
                )
              ]
        );
        $request =
            <<<QUERY
 {
  __type(name:"Query")
    {
    name
      kind
    fields(includeDeprecated:true){
      name
      type{
        kind
        name
      }
      description
      isDeprecated
      deprecationReason
      
    }
  }
}           

QUERY;
        $response = \GraphQL\GraphQL::executeQuery($testSchema, $request);
        $output = $response->toArray()['data']['__type'];
        $expectedResult =
            [
                "name" =>"Query",
                "kind" =>"OBJECT",
                "fields" => [
           [
            'name'=> 'deprecated',
            'type'=> [
                'kind'=> 'SCALAR',
                'name'=> 'String'
            ],
            'description'=> null,
            'isDeprecated'=> true,
            'deprecationReason'=> 'Deprecated in an older version'
           ],
           [
            'name'=> 'nonDeprecated',
            'type'=> [
                'kind'=> 'SCALAR',
                'name'=> 'String'
            ],
            'description'=> null,
            'isDeprecated'=> false,
            'deprecationReason'=> null
           ]
                ]
                ];
        $this->assertEquals($expectedResult, $output);
    }
}
