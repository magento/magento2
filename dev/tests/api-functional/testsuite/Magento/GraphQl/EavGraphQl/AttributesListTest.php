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
class AttributesListTest extends GraphQlAbstract
{
    private const ATTRIBUTE_NOT_FOUND_ERROR = "Attribute was not found in query result";

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute0'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute1'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute2'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute3'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute4'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => SalesSetup::CREDITMEMO_PRODUCT_ENTITY_TYPE_ID,
                'frontend_input' => 'boolean',
                'source_model' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean'
            ],
            'attribute5'
        )
    ]
    public function testAttributesList(): void
    {
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

        /** @var AttributeInterface $attribute */
        $attribute5 = DataFixtureStorageManager::getStorage()->get('attribute5');

        /** @var AttributeInterface $attribute */
        $attribute0 = DataFixtureStorageManager::getStorage()->get('attribute0');
        /** @var AttributeInterface $attribute */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute1');
        /** @var AttributeInterface $attribute */
        $attribute2 = DataFixtureStorageManager::getStorage()->get('attribute2');

        $this->assertEquals(
            $attribute0->getAttributeCode(),
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute0->getAttributeCode())['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );

        $this->assertEquals(
            $attribute1->getAttributeCode(),
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute1->getAttributeCode())['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $attribute2->getAttributeCode(),
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute2->getAttributeCode())['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute5->getAttributeCode())
        );

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
        $attribute3 = DataFixtureStorageManager::getStorage()->get('attribute3');
        /** @var AttributeInterface $attribute */
        $attribute4 = DataFixtureStorageManager::getStorage()->get('attribute4');

        $this->assertEquals(
            $attribute3->getAttributeCode(),
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute3->getAttributeCode())['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            $attribute4->getAttributeCode(),
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute4->getAttributeCode())['code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode($queryResult['attributesList']['items'], $attribute5->getAttributeCode())
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
