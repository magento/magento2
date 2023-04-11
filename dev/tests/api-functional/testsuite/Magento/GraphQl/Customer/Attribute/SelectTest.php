<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Eav\Test\Fixture\AttributeOption;
use Magento\EavGraphQl\Model\Uid;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Query\Uid as FrameworkUid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class SelectTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  attributesMetadata(input: {uids: ["%s"]}) {
    items {
      uid
      options {
        uid
        label
        value
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
            Attribute::class,
            [
                'entity_type_id' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'frontend_input' => 'select'
            ],
            'attribute'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$attribute.attribute_code$',
                'sort_order' => 10
            ],
            'option1'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$attribute.attribute_code$',
                'sort_order' => 20
            ],
            'option2'
        ),
    ]
    public function testMetadata(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');
        /** @var AttributeOptionInterface $option1 */
        $option1 = DataFixtureStorageManager::getStorage()->get('option1');
        /** @var AttributeOptionInterface $option2 */
        $option2 = DataFixtureStorageManager::getStorage()->get('option2');

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
                            'options' => [
                                $this->getOptionData($option1),
                                $this->getOptionData($option2)
                            ]
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }

    /**
     * @param AttributeOptionInterface $option
     * @return array
     */
    private function getOptionData(AttributeOptionInterface $option): array
    {
        return [
            'uid' => Bootstrap::getObjectManager()->get(FrameworkUid::class)->encode($option->getValue()),
            ...$option->getData()
        ];
    }
}
