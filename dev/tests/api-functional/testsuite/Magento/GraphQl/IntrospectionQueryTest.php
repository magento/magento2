<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl;


use Magento\TestFramework\TestCase\GraphQlAbstract;

class IntrospectionQueryTest extends GraphQlAbstract
{
    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testIntrospectionQueryWithFieldArgs()
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

        $response = $this->graphQlQuery($query);
        $schemaResponseFields = $response['__schema']['types'][0]['fields'];
        $expectedOutputArray = require __DIR__ . '/_files/query_introspection.php';
        foreach($expectedOutputArray as $searchTerm){
            $this->assertTrue((in_array($searchTerm, $schemaResponseFields)), 'Missing field array in the schema response');
        }
    }
}


