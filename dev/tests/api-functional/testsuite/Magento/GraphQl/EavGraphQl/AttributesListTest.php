<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

/**
 * Test EAV attributes metadata retrieval for entity type via GraphQL API
 */
#[
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customerAttribute0'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customerAttribute1'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customerAttribute2'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'customerAttribute3'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'catalogAttribute3'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'catalogAttribute4'
    ),
    DataFixture(
        Attribute::class,
        [
            'entity_type_id' => SalesSetup::CREDITMEMO_PRODUCT_ENTITY_TYPE_ID,
            'frontend_input' => 'boolean',
            'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
        ],
        'creditmemoAttribute5'
    )
]
class AttributesListTest extends GraphQlAbstract
{
    private const ATTRIBUTE_NOT_FOUND_ERROR = "Attribute was not found in query result";

    public function testAttributesListForCustomerEntityType(): void
    {
        /** @var AttributeInterface $attribute */
        $creditmemoAttribute5 = DataFixtureStorageManager::getStorage()->get('creditmemoAttribute5');

        /** @var AttributeInterface $attribute */
        $customerAttribute0 = DataFixtureStorageManager::getStorage()->get('customerAttribute0');
        /** @var AttributeInterface $attribute */
        $customerAttribute1 = DataFixtureStorageManager::getStorage()->get('customerAttribute1');
        /** @var AttributeInterface $attribute */
        $customerAttribute2 = DataFixtureStorageManager::getStorage()->get('customerAttribute2');
        /** @var AttributeInterface $attribute */
        $customerAttribute3 = DataFixtureStorageManager::getStorage()->get('customerAttribute3');
        $customerAttribute3->setIsVisible(false)->save();

        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CUSTOMER) {
                items {
                    uid
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
        $this->assertGreaterThanOrEqual(3, count($queryResult['attributesList']['items']));

        $this->assertEquals(
            $customerAttribute0->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $customerAttribute0->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );

        $this->assertEquals(
            $customerAttribute1->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $customerAttribute1->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $customerAttribute2->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $customerAttribute2->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $customerAttribute3->getAttributeCode()
            )
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $creditmemoAttribute5->getAttributeCode()
            )
        );
    }

    public function testAttributesListForCatalogProductEntityType(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            attributesList(entityType: CATALOG_PRODUCT) {
                items {
                    uid
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

        /** @var AttributeInterface $attribute */
        $creditmemoAttribute5 = DataFixtureStorageManager::getStorage()->get('creditmemoAttribute5');

        /** @var AttributeInterface $attribute */
        $catalogAttribute3 = DataFixtureStorageManager::getStorage()->get('catalogAttribute3');
        /** @var AttributeInterface $attribute */
        $catalogAttribute4 = DataFixtureStorageManager::getStorage()->get('catalogAttribute4');

        $this->assertEquals(
            $catalogAttribute3->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $catalogAttribute3->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $catalogAttribute4->getAttributeCode(),
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $catalogAttribute4->getAttributeCode()
            )['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode(
                $queryResult['attributesList']['items'],
                $creditmemoAttribute5->getAttributeCode()
            )
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
}
