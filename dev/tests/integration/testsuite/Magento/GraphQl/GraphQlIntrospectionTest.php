<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Type\Definition\InputObjectType;
use Magento\Framework\GraphQl\Type\Definition\ObjectType;
use Magento\Framework\GraphQl\Type\Definition\StringType;
use Magento\Framework\GraphQl\Type\SchemaFactory;
use Magento\Framework\ObjectManagerInterface;
use \GraphQL\Type\Definition\Type;

class GraphQlIntrospectionTest extends \PHPUnit\Framework\TestCase
{

    /** @var  SchemaFactory */
    private $schemaFactory;
    /** @var  ObjectManagerInterface */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->schemaFactory = $this->objectManager->create(SchemaFactory::class);
    }

    public function testIntrospectionQuery()
    {
        $emptySchema = $this->schemaFactory->create(
            [
                'query' => new ObjectType(
                    [
                        'name' => 'Query',
                        'fields' => ['a' => Type::string()]
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
                   'attributeA' =>['type' => Type::nonNull(Type::string()), 'description' =>'testDescriptionForA'],
                   'attributeB' => ['type' => Type::listOf(Type::string())],
                   'attributeC' => ['type' => Type::string(), 'defaultValue' => null ],
                   'attributeD' => ['type' => Type::string(), 'defaultValue' => 'test', 'description' =>'testDescriptionForD'],

               ]
            ]
        );
        $TestType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'field' => [
                    'type' => Type::string(),
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
     *  @SuppressWarnings(PHPMD.ExcessiveMethodLength)
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
                         'type' => Type::string(),
                         'deprecationReason' =>'Deprecated in an older version'
                       ],
                         'nonDeprecated' => [
                            'type' => Type::string()
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
