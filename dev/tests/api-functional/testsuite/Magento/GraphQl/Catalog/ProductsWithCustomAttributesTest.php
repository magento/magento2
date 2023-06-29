<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test class for product with custom attributes
 */
class ProductsWithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     */
    public function testQueryProductWithMultiSelectCustomAttribute()
    {
        if (!$this->cleanCache()) {
            $this->fail('Cache could not be cleaned properly.');
        }

        $multiSelectAttribute = "multiselect_attribute";
        $value = 'Option 2, Option 3, Option 4 "!@#$%^&*';
        $query = <<<QUERY
{
  products(filter: {sku: {eq: "simple_ms_2" }}) {
    items {
      name
      custom_attributes{
        code
        value
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertCount(1, $response['products']['items']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('custom_attributes', $response['products']['items'][0]);
        $customAttributes = $response['products']['items'][0]['custom_attributes'];
        $this->assertCustomAttribute($customAttributes, $multiSelectAttribute, $value);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_with_custom_attribute.php
     */
    public function testQueryProductWithTextCustomAttribute()
    {
        if (!$this->cleanCache()) {
            $this->fail('Cache could not be cleaned properly.');
        }
        $prductSku = 'simple';
        $textAttribute = "attribute_code_custom";
        $value = 'customAttributeValue';
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$prductSku}"}})
    {
        items
        {
            custom_attributes{
                code
                value
              }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertCount(1, $response['products']['items']);
        $this->assertArrayHasKey(0, $response['products']['items']);
        $this->assertArrayHasKey('custom_attributes', $response['products']['items'][0]);
        $customAttributes = $response['products']['items'][0]['custom_attributes'];
        $this->assertCustomAttribute($customAttributes, $textAttribute, $value);
    }

    /**
     * @param array $customAttributes
     * @param string $searchAttribute
     * @param string $actualValue
     */
    private function assertCustomAttribute($customAttributes, $searchAttribute, $actualValue)
    {
        $flag = false;
        $customAttributeValue = "";
        foreach ($customAttributes as $attributes) {
            if ($attributes['code'] == $searchAttribute) {
                $flag = true;
                $customAttributeValue = $attributes['value'];
                break;
            }
        }
        $this->assertStringContainsString($actualValue, $customAttributeValue);
        $this->assertEquals(true, $flag);
    }
}
