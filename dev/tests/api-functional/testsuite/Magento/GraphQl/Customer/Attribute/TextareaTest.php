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
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class TextareaTest extends GraphQlAbstract
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
        input_filter
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
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'textarea',
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus bibendum finibus' .
                    'quam, at vulputate quam feugiat tincidunt. Pellentesque venenatis nunc eget dolor' .
                    'dictum, vel ultricies orci facilisis. Sed hendrerit arcu tristique dui molestie, ' .
                    'sit amet scelerisque nibh scelerisque. Nulla sed tellus eget tellus volutpat ' .
                    'vestibulum. Mauris molestie erat sed odio maximus accumsan. Morbi velit felis, ' .
                    'tristique et lectus sollicitudin, laoreet aliquam nisl. Suspendisse vel ante at ' .
                    'metus mattis ultrices non nec libero. Cras odio nunc, eleifend vitae interdum a, ' .
                    'porttitor a dolor. Praesent mi odio, hendrerit quis consequat nec, vestibulum ' .
                    'vitae justo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin auctor' .
                    'ac quam id rhoncus. Proin vel orci eu justo cursus vestibulum.',
                'input_filter' => 'ESCAPEHTML',
                'sort_order' => 4,
            ],
            'attribute'
        )
    ]
    public function testMetadata(): void
    {
        /** @var AttributeMetadataInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $result = $this->graphQlQuery(sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer'));

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'TEXTAREA',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                            'input_filter' => $attribute->getInputFilter(),
                            'sort_order' => $attribute->getSortOrder(),
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
