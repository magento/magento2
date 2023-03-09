<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\EavGraphQl\Model\Uid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class MetadataTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  attributesMetadata(input: {uids: ["%s"]}) {
    items {
      uid
      code
      label
      entity_type
      frontend_input
      is_required
      default_value
      is_unique
      options {
        uid
        label
        value
        sort_order
      }
    }
    errors {
      type
      message
    }
  }
}
QRY;

    #[
        DataFixture(Attribute::class, as: 'attribute')
    ]
    public function testTextField(): void
    {
        /** @var ProductAttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $uid = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            'catalog_product',
            $attribute->getAttributeCode()
        );

        $this->assertEquals(
            [
                'attributesMetadata' => [
                    'items' => [
                        [
                            'uid' => $uid,
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getDefaultFrontendLabel(),
                            'entity_type' => 'CATALOG_PRODUCT',
                            'frontend_input' => 'TEXT',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                            'options' => []
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $this->graphQlQuery(sprintf(self::QUERY, $uid))
        );
    }

    public function testErrors(): void
    {
        $nonExistingEntity = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            'non_existing_entity_type',
            'name'
        );
        $nonExistingAttributeCode = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            'catalog_product',
            'non_existing_code'
        );
        $this->assertEquals(
            [
                'attributesMetadata' => [
                    'items' => [],
                    'errors' => [
                        [
                            'type' => 'INCORRECT_UID',
                            'message' => 'Value of uid "incorrect" is incorrect.'
                        ],
                        [
                            'type' => 'ENTITY_NOT_FOUND',
                            'message' => 'Entity "non_existing_entity_type" could not be found.'
                        ],
                        [
                            'type' => 'ATTRIBUTE_NOT_FOUND',
                            'message' => 'Attribute code "non_existing_code" could not be found.'
                        ],
                    ]
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    implode('","', ['incorrect', $nonExistingEntity, $nonExistingAttributeCode])
                )
            )
        );
    }
}
