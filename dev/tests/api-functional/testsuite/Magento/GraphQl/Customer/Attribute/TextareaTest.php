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
use Magento\EavGraphQl\Model\Uid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class TextareaTest extends GraphQlAbstract
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
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'textarea',
                'default_value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus bibendum finibus' .
                    'quam, at vulputate quam feugiat tincidunt. Pellentesque venenatis nunc eget dolor' .
                    'dictum, vel ultricies orci facilisis. Sed hendrerit arcu tristique dui molestie, ' .
                    'sit amet scelerisque nibh scelerisque. Nulla sed tellus eget tellus volutpat ' .
                    'vestibulum. Mauris molestie erat sed odio maximus accumsan. Morbi velit felis, ' .
                    'tristique et lectus sollicitudin, laoreet aliquam nisl. Suspendisse vel ante at ' .
                    'metus mattis ultrices non nec libero. Cras odio nunc, eleifend vitae interdum a, '.
                    'porttitor a dolor. Praesent mi odio, hendrerit quis consequat nec, vestibulum ' .
                    'vitae justo. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin auctor' .
                    'ac quam id rhoncus. Proin vel orci eu justo cursus vestibulum.'
            ],
            'attribute'
        )
    ]
    public function testMetadata(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        $uid = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            'customer',
            $attribute->getAttributeCode()
        );

        $result = $this->graphQlQuery(sprintf(self::QUERY, $uid));

        $this->assertEquals(
            [
                'attributesMetadata' => [
                    'items' => [
                        [
                            'uid' => $uid,
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getDefaultFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'TEXTAREA',
                            'is_required' => false,
                            'default_value' => $attribute->getDefaultValue(),
                            'is_unique' => false,
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
