<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class EntityTypeAttributesListTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
    {
        entityTypeAttributesList(entity_type: CUSTOMER) {
          items {
            uid
          }
          errors {
            type
            message
          }
        }
      }
QRY;

    #[
        DbIsolation(false),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            ],
            'attribute0'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            ],
            'attribute1'
        ),
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            ],
            'attribute2'
        )
    ]
    public function testAttributesList(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute0 = DataFixtureStorageManager::getStorage()->get('attribute0');

        /** @var AttributeInterface $attribute */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute1');

        /** @var AttributeInterface $attribute */
        $attribute2 = DataFixtureStorageManager::getStorage()->get('attribute2');

        $result = $this->graphQlQuery(self::QUERY);

        $this->assertEquals(
            [
                'entityTypeAttributesList' => [
                    'items' => [
                        [
                            "uid" => $attribute0->getAttributeId()
                        ],
                        [
                            "uid" => $attribute1->getAttributeId()
                        ],
                        [
                            "uid" => $attribute2->getAttributeId()
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
