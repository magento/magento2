<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class ImageTest extends GraphQlAbstract
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
      ... on CustomerAttributeMetadata {
        validate_rules {
          name
          value
        }
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
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'image',
                'validate_rules' => '{"MAX_FILE_SIZE":"10000","MAX_IMAGE_WIDTH":"100"}'
            ],
            'attribute'
        )
    ]
    public function testMetadata(): void
    {
        /** @var AttributeMetadataInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $formattedValidationRules = Bootstrap::getObjectManager()->get(FormatValidationRulesCommand::class)->execute(
            $attribute->getValidationRules()
        );

        $result = $this->graphQlQuery(sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer'));

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'IMAGE',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                            'validate_rules' => $formattedValidationRules
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
