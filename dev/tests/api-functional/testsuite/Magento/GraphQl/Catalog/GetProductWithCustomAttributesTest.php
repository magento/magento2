<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
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
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\EavGraphQl\Model\Uid as EAVUid;

/**
 * Test products with custom attributes query output
 */
#[
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'attribute_code' => 'product_custom_attribute',
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
     * @var ObjectManagerInterface
     */
    private $objectManager;

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
     * @var EAVUid $eavUid
     */
    private $eavUid;

    /**
     * @var Uid $uid
     */
    private $uid;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->uid = $this->objectManager->get(Uid::class);
        $this->eavUid = $this->objectManager->get(EAVUid::class);
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
            custom_attributes {
                uid
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        uid
                        label
                        value
                    }
                }
            }
        }
    }
}
QUERY;

        $response = $this->graphQlQuery($query);
        $this->assertArrayHasKey('items', $response['products'], 'Query result does not contain products');
        $this->assertGreaterThanOrEqual(2, count($response['products']['items'][0]['custom_attributes']));

        $this->assertResponseFields(
            $response['products']['items'][0],
            [
                'sku' => $this->product->getSku(),
                'name' => $this->product->getName()
            ]
        );

        $this->assertResponseFields(
            $this->getAttributeByCode(
                $response['products']['items'][0]['custom_attributes'],
                $this->varcharCustomAttribute->getAttributeCode()
            ),
            [
                'uid' => $this->eavUid->encode(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $this->varcharCustomAttribute->getAttributeCode()
                ),
                'code' => $this->varcharCustomAttribute->getAttributeCode(),
                'value' => 'test_value'
            ]
        );

        $this->assertResponseFields(
            $this->getAttributeByCode(
                $response['products']['items'][0]['custom_attributes'],
                $this->multiselectCustomAttribute->getAttributeCode()
            ),
            [
                'uid' => $this->eavUid->encode(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $this->multiselectCustomAttribute->getAttributeCode()
                ),
                'code' => $this->multiselectCustomAttribute->getAttributeCode(),
                'selected_options' => [
                    [
                        'uid' => $this->uid->encode($this->multiselectCustomAttributeOption2->getValue()),
                        'label' => $this->multiselectCustomAttributeOption2->getLabel(),
                        'value' => $this->multiselectCustomAttributeOption2->getValue(),
                    ],
                    [
                        'uid' => $this->uid->encode($this->multiselectCustomAttributeOption1->getValue()),
                        'label' => $this->multiselectCustomAttributeOption1->getLabel(),
                        'value' => $this->multiselectCustomAttributeOption1->getValue(),
                    ]
                ]
            ]
        );
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
            custom_attributes {
                uid
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        uid
                        label
                        value
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
            custom_attributes(filter: {is_visible_on_front: true}) {
                uid
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        uid
                        label
                        value
                    }
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
                            'custom_attributes' => [
                                [
                                    'uid' => $this->eavUid->encode(
                                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                                        $this->varcharCustomAttribute->getAttributeCode()
                                    ),
                                    'code' => $this->varcharCustomAttribute->getAttributeCode(),
                                    'value' => 'test_value'
                                ]
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
            custom_attributes(filter: {not_existing_filter: true}) {
                uid
                code
                ... on AttributeValue {
                    value
                }
                ... on AttributeSelectedOptions {
                    selected_options {
                        uid
                        label
                        value
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
}
