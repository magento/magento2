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
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Test\Fixture\Attribute;
use Magento\Eav\Test\Fixture\AttributeOption;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
class MultiselectTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  customAttributeMetadataV2(attributes: [{attribute_code: "%s", entity_type: "%s"}]) {
    items {
      code
      default_value
      options {
        label
        value
        is_default
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
                'frontend_input' => 'multiselect',
                'source_model' => Table::class
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
                'sort_order' => 20,
                'is_default' => true
            ],
            'option2'
        ),
        DataFixture(
            AttributeOption::class,
            [
                'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                'attribute_code' => '$attribute.attribute_code$',
                'sort_order' => 30,
                'is_default' => true
            ],
            'option3'
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
        /** @var AttributeOptionInterface $option3 */
        $option3 = DataFixtureStorageManager::getStorage()->get('option3');

        $result = $this->graphQlQuery(sprintf(self::QUERY, $attribute->getAttributeCode(), 'customer'));

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'default_value' => $option3->getValue() . ',' . $option2->getValue(),
                            'options' => [
                                $this->getOptionData($option1),
                                $this->getOptionData($option2),
                                $this->getOptionData($option3)
                            ]
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );

        $this->assertEquals($option2->getIsDefault(), true);
        $this->assertEquals($option3->getIsDefault(), true);
    }

    /**
     * @param AttributeOptionInterface $option
     * @return array
     */
    private function getOptionData(AttributeOptionInterface $option): array
    {
        return [
            'label' => $option->getLabel(),
            'value' => $option->getValue(),
            'is_default' => $option->getIsDefault()
        ];
    }
}
