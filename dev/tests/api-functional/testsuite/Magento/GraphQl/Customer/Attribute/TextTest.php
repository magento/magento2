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
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class TextTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  customAttributeMetadataV2(attributes: [{attribute_code: "%s", entity_type: "%s"}]) {
    items {
      code
      label
      entity_type
      frontend_input
      is_required
      default_value
      is_unique
    }
    errors {
      type
      message
    }
  }
}
QRY;

    #[
        DataFixture(
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
            ],
            'attribute'
        )
    ]
    public function testTextField(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getDefaultFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'TEXT',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $this->graphQlQuery(sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer'))
        );
    }

    public function testErrorEntityNotFound(): void
    {
        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [],
                    'errors' => [
                        [
                            'type' => 'ENTITY_NOT_FOUND',
                            'message' => 'Entity "non_existing_entity_type" could not be found.'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    'lastname',
                    'non_existing_entity_type'
                )
            )
        );
    }

    public function testErrorAttributeNotFound(): void
    {
        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [],
                    'errors' => [
                        [
                            'type' => 'ATTRIBUTE_NOT_FOUND',
                            'message' => 'Attribute code "non_existing_code" could not be found.'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    'non_existing_code',
                    'customer'
                )
            )
        );
    }
}
