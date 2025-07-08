<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\GraphQl\EavGraphQl;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Sales\Setup\SalesSetup;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\Catalog\Test\Fixture\Attribute as ProductAttribute;
use Magento\Customer\Test\Fixture\CustomerAttribute;

/**
 * Test EAV attributes metadata retrieval for entity type via GraphQL API
 */
#[
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customer_attribute_0'
    ),
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customer_attribute_1'
    ),
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customer_attribute_2'
    ),
    DataFixture(
        CustomerAttribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customer_attribute_3'
    ),
    DataFixture(
        ProductAttribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'is_visible_on_front' => 1,
        ],
        'catalog_attribute_3'
    ),
    DataFixture(
        ProductAttribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
            'is_visible_on_front' => 1,
            'is_comparable' => 1
        ],
        'catalog_attribute_4'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => SalesSetup::CREDITMEMO_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'credit_memo_attribute_5'
    )
]
class AttributesListTest extends GraphQlAbstract
{
    private const ATTRIBUTE_NOT_FOUND_ERROR = "Attribute was not found in query result";

    /**
     * @var AttributeInterface|null
     */
    private $creditmemoAttribute5;

    /**
     * @var AttributeInterface|null
     */
    private $customerAttribute0;

    /**
     * @var AttributeInterface|null
     */
    private $customerAttribute1;

    /**
     * @var AttributeInterface|null
     */
    private $customerAttribute2;

    /**
     * @var AttributeInterface|null
     */
    private $customerAttribute3;

    /**
     * @var AttributeInterface|null
     */
    private $catalogAttribute3;

    /**
     * @var AttributeInterface|null
     */
    private $catalogAttribute4;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->creditmemoAttribute5 = DataFixtureStorageManager::getStorage()->get('credit_memo_attribute_5');
        $this->customerAttribute0 = DataFixtureStorageManager::getStorage()->get('customer_attribute_0');
        $this->customerAttribute1 = DataFixtureStorageManager::getStorage()->get('customer_attribute_1');
        $this->customerAttribute2 = DataFixtureStorageManager::getStorage()->get('customer_attribute_2');
        $this->customerAttribute3 = DataFixtureStorageManager::getStorage()->get('customer_attribute_3');
        $this->customerAttribute3->setIsVisible(false)->save();
        $this->catalogAttribute3 = DataFixtureStorageManager::getStorage()->get('catalog_attribute_3');
        $this->catalogAttribute4 = DataFixtureStorageManager::getStorage()->get('catalog_attribute_4');
    }

    public function testAttributesListForCustomerEntityType(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CUSTOMER) {
                items {
                    code
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);
        $this->assertCustomerResults($queryResult);
        $this->assertEmpty(count($queryResult['attributesList']['errors']));
    }

    public function testAttributesListForCatalogProductEntityType(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CATALOG_PRODUCT) {
                items {
                    code
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);
        $this->assertArrayHasKey('items', $queryResult['attributesList'], 'Query result does not contain items');
        $this->assertGreaterThanOrEqual(2, count($queryResult['attributesList']['items']));

        $this->assertEquals(
            $this->catalogAttribute3->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->catalogAttribute3->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $this->catalogAttribute4->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->catalogAttribute4->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->creditmemoAttribute5->getAttributeCode()
            )
        );
    }

    public function testAttributesListFilterForCatalogProductEntityType(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CATALOG_PRODUCT, filters: {is_visible_on_front: true, is_comparable: true}) {
                items {
                    code
                    ... on CatalogAttributeMetadata {
                        is_comparable
                        is_visible_on_front
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);
        $this->assertArrayHasKey('items', $queryResult['attributesList'], 'Query result does not contain items');
        $this->assertEquals(
            [
                'attributesList' => [
                    'items' => [
                        0  => [
                            'code' => $this->catalogAttribute4->getAttributeCode(),
                            'is_comparable' => true,
                            'is_visible_on_front' => true
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $queryResult
        );
    }

    public function testAttributesListAnyFilterApply(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CUSTOMER, filters: {is_filterable: true}) {
                items {
                    code
                    ... on CatalogAttributeMetadata {
                        is_filterable
                    }
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);
        $this->assertCustomerResults($queryResult);
        $this->assertEquals(1, count($queryResult['attributesList']['errors']));
        $this->assertEquals('FILTER_NOT_FOUND', $queryResult['attributesList']['errors'][0]['type']);
        $this->assertEquals(
            'Cannot filter by "is_filterable" as that field does not belong to "customer".',
            $queryResult['attributesList']['errors'][0]['message']
        );
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
        return $attribute[array_key_first($attribute)] ?? [];
    }

    /**
     * @param array $queryResult
     */
    private function assertCustomerResults(array $queryResult): void
    {
        $this->assertArrayHasKey('items', $queryResult['attributesList'], 'Query result does not contain items');
        $this->assertGreaterThanOrEqual(3, count($queryResult['attributesList']['items']));

        $this->assertEquals(
            $this->customerAttribute0->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->customerAttribute0->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );

        $this->assertEquals(
            $this->customerAttribute1->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->customerAttribute1->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $this->customerAttribute2->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->customerAttribute2->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->customerAttribute3->getAttributeCode()
            )
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $this->creditmemoAttribute5->getAttributeCode()
            )
        );
    }
}
