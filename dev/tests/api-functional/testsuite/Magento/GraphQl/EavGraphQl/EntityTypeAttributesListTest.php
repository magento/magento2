<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Sales\Setup\SalesSetup;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test EAV attributes metadata retrieval for entity type via GraphQL API
 */
class EntityTypeAttributesListTest extends GraphQlAbstract
{
    private const ATTRIBUTE_NOT_FOUND_ERROR = "Attribute was not found in query result";

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'attribute_0'
            ],
            'attribute0'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'attribute_1'
            ],
            'attribute1'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => 'attribute_2'
            ],
            'attribute2'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'attribute_3'
            ],
            'attribute3'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'attribute_4'
            ],
            'attribute4'
        )
        ,
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => SalesSetup::CREDITMEMO_PRODUCT_ENTITY_TYPE_ID,
                'attribute_code' => 'attribute_5'
            ],
            'attribute5'
        )
    ]
    public function testEntityTypeAttributesList(): void
    {
        $queryResult = $this->graphQlQuery(<<<QRY
        {
            entityTypeAttributesList(entity_type: CUSTOMER) {
                items {
                    uid
                    attribute_code
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);

        $this->assertArrayHasKey('items', $queryResult['entityTypeAttributesList'], 'Query result does not contain items');
        $this->assertGreaterThanOrEqual(3, count($queryResult['entityTypeAttributesList']['items']));

        $this->assertEquals(
            'attribute_0',
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_0')['attribute_code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        
        $this->assertEquals(
            'attribute_1',
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_1')['attribute_code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            'attribute_2',
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_2')['attribute_code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_5')
        );

        $queryResult = $this->graphQlQuery(<<<QRY
        {
            entityTypeAttributesList(entity_type: CATALOG_PRODUCT) {
                items {
                    uid
                    attribute_code
                }
                errors {
                    type
                    message
                }
            }
        }
QRY);
        $this->assertArrayHasKey('items', $queryResult['entityTypeAttributesList'], 'Query result does not contain items');
        $this->assertGreaterThanOrEqual(2, count($queryResult['entityTypeAttributesList']['items']));

        $this->assertEquals(
            'attribute_3',
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_3')['attribute_code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            'attribute_4',
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_4')['attribute_code'],
            self::ATTRIBUTE_NOT_FOUND_ERROR
        );
        $this->assertEquals(
            [],
            $this->getAttributeByCode($queryResult['entityTypeAttributesList']['items'], 'attribute_5')
        );
    }

    /**
     * Finds attribute in query result
     * 
     * @param array $items
     * @param string $attribute_code
     * @return array
     */
    private function getAttributeByCode($items, $attribute_code)
    {
        $attribute = array_filter($items, function ($item) use ($attribute_code) {
            return $item['attribute_code'] == $attribute_code;
        });
        return $attribute[array_key_first($attribute)] ?? [];
    }
}
