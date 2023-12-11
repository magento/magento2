<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class IntrospectionQueryTest extends GraphQlAbstract
{
    /**
     * Tests that Introspection is allowed by default
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectionQuery()
    {
        $query
            = <<<QUERY
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
        $this->assertArrayHasKey('__schema', $this->graphQlQuery($query));
    }

    /**
     * Tests that Introspection is allowed by default
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectionQueryWithOnlySchema()
    {
        $query
            = <<<QUERY
 {
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
        $this->assertArrayHasKey('__schema', $this->graphQlQuery($query));
        $response = $this->graphQlQuery($query);

        $query
            = <<<QUERY
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
        $this->assertArrayHasKey('__schema', $this->graphQlQuery($query));
        $responseFields = $this->graphQlQuery($query);
        $this->assertResponseFields($response, $responseFields);
        $this->assertEquals($responseFields, $response);
    }

    /**
     * Tests that Introspection is allowed by default
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectionQueryWithOnlyType()
    {
        $query
            = <<<QUERY
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
        $this->assertArrayHasKey('__type', $this->graphQlQuery($query));
        $response = $this->graphQlQuery($query);
        $this->assertNotEmpty($response['__type']['fields']);
    }

    /**
     * Tests that Introspection Query with deprecated annotations on enum values, fields are read.
     */
    public function testIntrospectionQueryWithDeprecatedAnnotationOnEnumAndFieldValues()
    {
        $query
            = <<<QUERY
 query IntrospectionQuery {
    __schema {
      queryType { name }
      mutationType { name }
      types {
        ...FullType
      }
    }
  }
  fragment FullType on __Type {
    kind
    name
    description
    fields(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
    enumValues(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
  }
  
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('__schema', $response);
        $schemaResponseFields = $response['__schema']['types'];
        $enumValueReasonArray = $this->getEnumValueDeprecatedReason($schemaResponseFields);
        $fieldsValueReasonArray = $this->getFieldsValueDeprecatedReason($schemaResponseFields);
        $expectedOutput = require __DIR__ . '/_files/schema_response_sdl_deprecated_annotation.php';

        // checking field values deprecated reason
        $fieldDeprecatedReason = [];
        $fieldsArray = $expectedOutput[0]['fields'];
        foreach ($fieldsArray as $field) {
            if ($field['isDeprecated'] === true) {
                $fieldDeprecatedReason [] = $field['deprecationReason'];
            }
        }
        $this->assertNotEmpty($fieldDeprecatedReason);
        $this->assertContains(
            'Symbol was missed. Use `default_display_currency_code`.',
            $fieldDeprecatedReason
        );

        $this->assertContains(
            'Symbol was missed. Use `default_display_currency_code`.',
            $fieldsValueReasonArray
        );

        $this->assertNotEmpty(
            array_intersect($fieldDeprecatedReason, $fieldsValueReasonArray)
        );

        // checking enum values deprecated reason
        $enumValueDeprecatedReason = [];
        $enumValuesArray = $expectedOutput[1]['enumValues'];
        foreach ($enumValuesArray as $enumValue) {
            if ($enumValue['isDeprecated'] === true) {
                $enumValueDeprecatedReason [] = $enumValue['deprecationReason'];
            }
        }
        $this->assertNotEmpty($enumValueDeprecatedReason);
        $this->assertContains(
            '`sample_url` serves to get the downloadable sample',
            $enumValueDeprecatedReason
        );
        $this->assertContains(
            '`sample_url` serves to get the downloadable sample',
            $enumValueReasonArray
        );
        $this->assertNotEmpty(
            array_intersect($enumValueDeprecatedReason, $enumValueReasonArray)
        );
    }

    /**
     * Get the enum values deprecated reasons from the schema
     *
     * @param array $schemaResponseFields
     * @return array
     */
    private function getEnumValueDeprecatedReason($schemaResponseFields): array
    {
        $enumValueReasonArray = [];
        foreach ($schemaResponseFields as $schemaResponseField) {
            if (!empty($schemaResponseField['enumValues'])) {
                foreach ($schemaResponseField['enumValues'] as $enumValueDeprecationReasonArray) {
                    if (!empty($enumValueDeprecationReasonArray['deprecationReason'])) {
                        $enumValueReasonArray[] = $enumValueDeprecationReasonArray['deprecationReason'];
                    }
                }
            }
        }
        return $enumValueReasonArray;
    }

    /**
     * Get the fields values deprecated reasons from the schema
     *
     * @param array $schemaResponseFields
     * @return array
     */
    private function getFieldsValueDeprecatedReason($schemaResponseFields): array
    {
        $fieldsValueReasonArray = [];
        foreach ($schemaResponseFields as $schemaResponseField) {
            if (!empty($schemaResponseField['fields'])) {
                foreach ($schemaResponseField['fields'] as $fieldsValueDeprecatedReasonArray) {
                    if (!empty($fieldsValueDeprecatedReasonArray['deprecationReason'])) {
                        $fieldsValueReasonArray[] = $fieldsValueDeprecatedReasonArray['deprecationReason'];
                    }
                }
            }
        }
        return $fieldsValueReasonArray;
    }
}
