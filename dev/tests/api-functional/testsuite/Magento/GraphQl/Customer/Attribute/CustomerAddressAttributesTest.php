<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class CustomerAddressAttributesTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  customAttributeMetadataV2(attributes: [{attribute_code: "%s", entity_type: "%s"}]) {
    items {
      code
      label
      entity_type
      frontend_input
      frontend_class
      is_required
      default_value
      is_unique
      ... on CustomerAttributeMetadata {
        input_filter
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
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'frontend_input' => 'date',
                'frontend_class' => 'hidden-for-virtual',
                'default_value' => '2023-03-22 00:00:00',
                'input_filter' => 'DATE',
                'validate_rules' =>
                    '{"DATE_RANGE_MIN":"1679443200","DATE_RANGE_MAX":"1679875200","INPUT_VALIDATION":"DATE"}'
            ],
            'attribute'
        ),
    ]
    public function testMetadata(): void
    {
        /** @var AttributeMetadataInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $formattedValidationRules = Bootstrap::getObjectManager()->get(FormatValidationRulesCommand::class)->execute(
            $attribute->getValidationRules()
        );

        $result = $this->graphQlQuery(
            sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer_address')
        );

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getFrontendLabel(),
                            'entity_type' => 'CUSTOMER_ADDRESS',
                            'frontend_input' => 'DATE',
                            'frontend_class' => 'hidden-for-virtual',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                            'input_filter' => $attribute->getInputFilter(),
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
