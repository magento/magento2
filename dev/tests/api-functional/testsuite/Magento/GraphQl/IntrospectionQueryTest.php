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
     * Tests that Introspection is disabled when not in developer mode
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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'GraphQL response contains errors: GraphQL introspection is not allowed, but ' .
            'the query contained __schema or __type'
        );
        $this->graphQlQuery($query);
    }
}
