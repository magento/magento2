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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Store;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test customer EAV attribute options retrieval via GraphQL API
 */
#[
    DataFixture(Store::class, as: 'store_1'),
    DataFixture(Store::class, as: 'store_2'),
    DataFixture(
        Attribute::class,
        [
            'frontend_labels' => [
                [
                    'store_id' => 0,
                    'label' => 'height'
                ],
                [
                    'store_id' => '$store_1.id$',
                    'label' => 'hair'
                ],
                [
                    'store_id' => '$store_2.id$',
                    'label' => 'eyes'
                ],
            ],
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
            'sort_order' => 10,
            'store_labels' => [
                [
                    'store_id' => 0,
                    'label' => 'tall'
                ],
                [
                    'store_id' => '$store_1.id$',
                    'label' => 'red'
                ],
                [
                    'store_id' => '$store_2.id$',
                    'label' => 'green'
                ]
            ],
        ],
        'option1'
    ),
    DataFixture(
        AttributeOption::class,
        [
            'entity_type' => CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            'attribute_code' => '$attribute.attribute_code$',
            'sort_order' => 20,
            'store_labels' => [
                [
                    'store_id' => 0,
                    'label' => 'short'
                ],
                [
                    'store_id' => '$store_1.id$',
                    'label' => 'brown'
                ],
                [
                    'store_id' => '$store_2.id$',
                    'label' => 'blue'
                ]
            ],
        ],
        'option2'
    ),
]
class StoreViewOptionsTest extends GraphQlAbstract
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
      options {
        label
      }
    }
    errors {
      type
      message
    }
  }
}
QRY;

    public function testAttributeLabelsNoStoreViews(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        /** @var AttributeOptionInterface $option1 */
        $option1 = DataFixtureStorageManager::getStorage()->get('option1');

        /** @var AttributeOptionInterface $option2 */
        $option2 = DataFixtureStorageManager::getStorage()->get('option2');

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getDefaultFrontendLabel(),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'SELECT',
                            'is_required' => false,
                            'default_value' => '',
                            'is_unique' => false,
                            'options' => [
                                [
                                    'label' => $option1->getLabel()
                                ],
                                [
                                    'label' => $option2->getLabel()
                                ],
                            ]
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    $attribute->getAttributeCode(),
                    'customer'
                )
            )
        );
    }

    public function testAttributeLabelsMultipleStoreViews(): void
    {
        /** @var AttributeInterface $attribute */
        $attribute = DataFixtureStorageManager::getStorage()->get('attribute');

        /** @var StoreInterface $store1 */
        $store1 = DataFixtureStorageManager::getStorage()->get('store_1');

        /** @var StoreInterface $store2 */
        $store2 = DataFixtureStorageManager::getStorage()->get('store_2');

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getStoreLabel($store1->getId()),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'SELECT',
                            'is_required' => false,
                            'default_value' => '',
                            'is_unique' => false,
                            'options' => [
                                [
                                    'label' => 'red'
                                ],
                                [
                                    'label' => 'brown'
                                ],
                            ]
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    $attribute->getAttributeCode(),
                    'customer'
                ),
                [],
                '',
                ['Store' => $store1->getCode()]
            )
        );

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'code' => $attribute->getAttributeCode(),
                            'label' => $attribute->getStoreLabel($store2->getId()),
                            'entity_type' => 'CUSTOMER',
                            'frontend_input' => 'SELECT',
                            'is_required' => false,
                            'default_value' => '',
                            'is_unique' => false,
                            'options' => [
                                [
                                    'label' => 'green'
                                ],
                                [
                                    'label' => 'blue'
                                ],
                            ]
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $this->graphQlQuery(
                sprintf(
                    self::QUERY,
                    $attribute->getAttributeCode(),
                    'customer'
                ),
                [],
                '',
                ['Store' => $store2->getCode()]
            )
        );
    }
}
