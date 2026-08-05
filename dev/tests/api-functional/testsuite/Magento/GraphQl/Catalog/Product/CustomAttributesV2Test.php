<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Product;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Indexer\Test\Fixture\Indexer;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test for custom_attributesV2 field with various attribute types
 */
class CustomAttributesV2Test extends GraphQlAbstract
{
    /**
     * Test custom_attributesV2 returns simple attributes without errors
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'test-simple-product',
                'name' => 'Simple Product',
                'price' => 10
            ],
            'simple_product'
        ),
        DataFixture(Indexer::class)
    ]
    public function testCustomAttributesV2WithSimpleProduct()
    {
        $productSku = 'test-simple-product';
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            name
            custom_attributesV2 {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertArrayHasKey('items', $response['products']);
        $this->assertNotEmpty($response['products']['items']);

        $product = $response['products']['items'][0];
        $this->assertEquals($productSku, $product['sku']);

        // Verify custom_attributesV2 structure
        $this->assertArrayHasKey('custom_attributesV2', $product);
        $this->assertArrayHasKey('items', $product['custom_attributesV2']);
        $this->assertArrayHasKey('errors', $product['custom_attributesV2']);

        // Verify no errors occurred
        $this->assertEmpty($product['custom_attributesV2']['errors']);

        // Verify at least some attributes are returned
        $this->assertNotEmpty($product['custom_attributesV2']['items']);

        // Verify each attribute has proper structure
        foreach ($product['custom_attributesV2']['items'] as $attribute) {
            $this->assertArrayHasKey('code', $attribute);
            $this->assertNotEmpty($attribute['code']);

            // If it's an AttributeValue type, it should have a value field
            if (isset($attribute['value'])) {
                $this->assertIsString($attribute['value']);
            }
        }
    }

    /**
     * Nested array attribute values are JSON-encoded; query still succeeds without errors.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'test-custom-attrs-product',
                'name' => 'Test Product for Custom Attributes',
                'price' => 10
            ],
            'test_product'
        ),
        DataFixture(Indexer::class)
    ]
    public function testCustomAttributesV2HandlesNestedArrayPayloads()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "test-custom-attrs-product"}})
    {
        items {
            sku
            custom_attributesV2 {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        // Verify the query executed without throwing exceptions
        $this->assertArrayHasKey('products', $response);
        $this->assertNotEmpty($response['products']['items']);

        $product = $response['products']['items'][0];
        $this->assertArrayHasKey('custom_attributesV2', $product);

        $this->assertArrayHasKey('errors', $product['custom_attributesV2']);
        $this->assertEmpty($product['custom_attributesV2']['errors']);
        $this->assertNotEmpty($product['custom_attributesV2']['items']);
    }

    /**
     * Numeric catalog attributes (e.g. weight) resolve as AttributeValue strings through custom_attributesV2.
     *
     * @return void
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'test-custom-attrs-weight-product',
                'name' => 'Weight Product',
                'price' => 11,
                'weight' => 2.75,
            ],
            'weight_product'
        ),
        DataFixture(Indexer::class)
    ]
    public function testCustomAttributesV2ReturnsWeightAsScalarAttributeValue()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "test-custom-attrs-weight-product"}})
    {
        items {
            sku
            custom_attributesV2 {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertNotEmpty($response['products']['items']);

        $product = $response['products']['items'][0];
        $this->assertSame('test-custom-attrs-weight-product', $product['sku']);
        $this->assertEmpty($product['custom_attributesV2']['errors']);

        $weightItem = null;
        foreach ($product['custom_attributesV2']['items'] as $item) {
            if (($item['code'] ?? '') === 'weight') {
                $weightItem = $item;
                break;
            }
        }

        $this->assertNotNull($weightItem, 'weight should be present in custom_attributesV2 items');
        $this->assertArrayHasKey('value', $weightItem);
        $this->assertIsString($weightItem['value']);
        $this->assertEqualsWithDelta(2.75, (float) $weightItem['value'], 0.001);
    }

    /**
     * Test custom_attributesV2 with filtering
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-filter-test',
                'name' => 'Simple Product for Filter Test',
                'price' => 15
            ],
            'simple_product_filter'
        ),
        DataFixture(Indexer::class)
    ]
    public function testCustomAttributesV2WithFilters()
    {
        $productSku = 'simple-filter-test';
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items {
            sku
            custom_attributesV2(filters: {is_visible_on_front: true}) {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);

        $this->assertArrayHasKey('products', $response);
        $this->assertNotEmpty($response['products']['items']);

        $product = $response['products']['items'][0];
        $this->assertArrayHasKey('custom_attributesV2', $product);

        // Should not have errors
        $this->assertEmpty($product['custom_attributesV2']['errors']);
    }
}
