<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ExceptionFormatterDefaultModeTest extends GraphQlAbstract
{
    public function testDuplicateEntityTypeException()
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      entity_type:"catalog_category"
      entity_type:"catalog_product"
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: There' . ' ' .
            'can be only one input field named "entity_type"');
        $this->graphQlQuery($query);
    }

    public function testEmptyAttributeInputException()
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Missing attribute_code/entity_type for the ' .
            'input Empty AttributeInput.');

        $this->graphQlQuery($query);
    }
    public function testAttributeWithNoEntityTypeInputException()
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      attribute_code:"sku"
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Missing entity_type for the input' .
            ' attribute_code: sku.');

        $this->graphQlQuery($query);
    }

    public function testAttributeWithNoAttributeCodeInputException()
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      entity_type:"catalog_category"
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Missing attribute_code for the input ' .
            'entity_type: catalog_category.');

        $this->graphQlQuery($query);
    }

    public function testInvalidEntityTypeException()
    {
        $query
            = <<<QUERY
  {
  customAttributeMetadata(attributes:[
    {
      attribute_code:"sku"
      entity_type:"invalid"
    }
  ])
    {
      items{        
      attribute_code
      attribute_type
      entity_type
    }      
    }  
  }
QUERY;
        $this->expectException(\Exception::class);

        $this->expectExceptionMessage('Invalid entity_type specified: invalid');

        $this->graphQlQuery($query);
    }
}
