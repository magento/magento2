<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\Catalog\Test\Fixture\MultiselectAttribute;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\AttributeOption as AttributeOptionFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test products with custom attributes query output
 */
#[
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => 'product_custom_attribute',
            'is_comparable' => 1,
            'is_visible_on_front' => 1
        ],
        'varchar_custom_attribute'
    ),
    DataFixture(
        MultiselectAttribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'source_model' => Table::class,
            'backend_model' => ArrayBackend::class,
            'attribute_code' => 'product_custom_attribute_multiselect'
        ],
        'multiselect_custom_attribute'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
            'label' => 'red',
            'sort_order' => 20
        ],
        'multiselect_custom_attribute_option_1'
    ),
    DataFixture(
        AttributeOptionFixture::class,
        [
            'entity_type' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
            'sort_order' => 10,
            'label' => 'white',
            'is_default' => true
        ],
        'multiselect_custom_attribute_option_2'
    ),
    DataFixture(
        ProductFixture::class,
        [
            'custom_attributes' => [
                [
                    'attribute_code' => '$varchar_custom_attribute.attribute_code$',
                    'value' => 'test_value'
                ],
                [
                    'attribute_code' => '$multiselect_custom_attribute.attribute_code$',
                    'selected_options' => [
                        ['value' => '$multiselect_custom_attribute_option_1.value$'],
                        ['value' => '$multiselect_custom_attribute_option_2.value$']
                    ],
                ],
            ],
        ],
        'product'
    ),
]
class GetProductWithCustomAttributesTest extends GraphQlAbstract
{
    /**
     * @var AttributeInterface|null
     */
    private $varcharCustomAttribute;

    /**
     * @var AttributeInterface|null
     */
    private $multiselectCustomAttribute;

    /**
     * @var AttributeOptionInterface|null
     */
    private $multiselectCustomAttributeOption1;

    /**
     * @var AttributeOptionInterface|null
     */
    private $multiselectCustomAttributeOption2;

    /**
     * @var Product|null
     */
    private $product;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->varcharCustomAttribute = DataFixtureStorageManager::getStorage()->get(
            'varchar_custom_attribute'
        );
        $this->multiselectCustomAttribute = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_attribute'
        );
        $this->multiselectCustomAttributeOption1 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_attribute_option_1'
        );
        $this->multiselectCustomAttributeOption2 = DataFixtureStorageManager::getStorage()->get(
            'multiselect_custom_attribute_option_2'
        );

        $this->product = DataFixtureStorageManager::getStorage()->get('product');
    }

    public function testGetProductWithCustomAttributes()
    {
        $productSku = $this->product->getSku();

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items
        {
            sku
            name
            custom_attributesV2 {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                    ... on AttributeSelectedOptions {
                        selected_options {
                            label
                            value
                        }
                    }
                },
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

        $this->assertProductCustomAttributesResult($response);
        $this->assertEmpty(count($response['products']['items'][0]['custom_attributesV2']['errors']));
    }

    public function testGetNoResultsWhenFilteringByNotExistingSku()
    {
        $query = <<<QUERY
{
    products(filter: {sku: {eq: "not_existing_sku"}})
    {
        items
        {
            sku
            name
            custom_attributesV2 {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                    ... on AttributeSelectedOptions {
                        selected_options {
                            label
                            value
                        }
                    }
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('items', $response['products'], 'Query result must not contain products');
        $this->assertCount(0, $response['products']['items']);
    }

    public function testGetProductCustomAttributesFiltered()
    {
        $productSku = $this->product->getSku();

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items
        {
            sku
            name
            custom_attributesV2(filters: {is_comparable: true, is_visible_on_front: true}) {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                    ... on AttributeSelectedOptions {
                        selected_options {
                            label
                            value
                        }
                    }
                },
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
        $this->assertEquals(
            [
                'products' => [
                    'items' => [
                        0 => [
                            'sku' => $this->product->getSku(),
                            'name' => $this->product->getName(),
                            'custom_attributesV2' => [
                                'items' => [
                                    0 => [
                                        'code' => $this->varcharCustomAttribute->getAttributeCode(),
                                        'value' => 'test_value'
                                    ]
                                ],
                                'errors' => []
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetProductCustomAttributesFilteredByNotExistingField()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Field "not_existing_filter" is not defined by type "AttributeFilterInput"');
        $productSku = $this->product->getSku();

        $query = <<<QUERY
{
    products(filter: {sku: {eq: "{$productSku}"}})
    {
        items
        {
            sku
            name
            custom_attributesV2(filters: {not_existing_filter: true}) {
                items {
                    code
                    ... on AttributeValue {
                        value
                    }
                    ... on AttributeSelectedOptions {
                        selected_options {
                            label
                            value
                        }
                    }
                }
            }
        }
    }
}
QUERY;

        $this->graphQlQuery($query);
    }

    /**
     * Finds attribute in query result
     *
     * @param array $items
     * @param string $attribute_code
     * @return array
     */
    private function getAttributeByCode(array $items, string $attribute_code): array
    {
        $attribute = array_filter($items, function ($item) use ($attribute_code) {
            return $item['code'] == $attribute_code;
        });

        return array_merge(...$attribute);
    }

    /**
     * @param array $response
     */
    private function assertProductCustomAttributesResult(array $response): void
    {
        $this->assertArrayHasKey('items', $response['products'], 'Query result does not contain products');
        $this->assertArrayHasKey(
            'items',
            $response['products']['items'][0]['custom_attributesV2'],
            'Query result does not contain custom attributes'
        );
        $this->assertGreaterThanOrEqual(2, count($response['products']['items'][0]['custom_attributesV2']['items']));

        $this->assertResponseFields(
            $response['products']['items'][0],
            [
                'sku' => $this->product->getSku(),
                'name' => $this->product->getName()
            ]
        );

        $this->assertResponseFields(
            $this->getAttributeByCode(
                $response['products']['items'][0]['custom_attributesV2']['items'],
                $this->varcharCustomAttribute->getAttributeCode()
            ),
            [
                'code' => $this->varcharCustomAttribute->getAttributeCode(),
                'value' => 'test_value'
            ]
        );

        $this->assertResponseFields(
            $this->getAttributeByCode(
                $response['products']['items'][0]['custom_attributesV2']['items'],
                $this->multiselectCustomAttribute->getAttributeCode()
            ),
            [
                'code' => $this->multiselectCustomAttribute->getAttributeCode(),
                'selected_options' => [
                    [
                        'label' => $this->multiselectCustomAttributeOption2->getLabel(),
                        'value' => $this->multiselectCustomAttributeOption2->getValue(),
                    ],
                    [
                        'label' => $this->multiselectCustomAttributeOption1->getLabel(),
                        'value' => $this->multiselectCustomAttributeOption1->getValue(),
                    ]
                ]
            ]
        );
    }
}
