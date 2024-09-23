<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Customer\Attribute;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Test\Fixture\CustomerAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test customer EAV form attributes metadata retrieval via GraphQL API
 */
class AttributesFormTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  attributesForm(formCode: "%s") {
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
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address']
            ],
            'attribute_1'
        ),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_address_edit']
            ],
            'attribute_2'
        )
    ]
    public function testAttributesForm(): void
    {
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');
        /** @var AttributeInterface $attribute2 */
        $attribute2 = DataFixtureStorageManager::getStorage()->get('attribute_2');

        $result = $this->graphQlQuery(sprintf(self::QUERY, 'customer_register_address'));

        $this->assertNotEmpty($result['attributesForm']['items']);
        $codes = $this->getAttributeCodes($result['attributesForm']['items']);

        $this->assertContains($attribute1->getAttributeCode(), $codes);
        $this->assertContains('country_id', $codes);
        $this->assertContains('region_id', $codes);
        $this->assertNotContains($attribute2->getAttributeCode(), $codes);
    }

    public function testAttributesFormAdminHtmlForm(): void
    {
        $this->assertEquals(
            [
                'attributesForm' => [
                    'items' => [],
                    'errors' => [
                        [
                            'type' => 'ENTITY_NOT_FOUND',
                            'message' => 'Form "adminhtml_customer" could not be found.'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(sprintf(self::QUERY, 'adminhtml_customer'))
        );
    }

    public function testAttributesFormDoesNotExist(): void
    {
        $this->assertEquals(
            [
                'attributesForm' => [
                    'items' => [],
                    'errors' => [
                        [
                            'type' => 'ENTITY_NOT_FOUND',
                            'message' => 'Form "not_existing_form" could not be found.'
                        ]
                    ]
                ]
            ],
            $this->graphQlQuery(sprintf(self::QUERY, 'not_existing_form'))
        );
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'store_group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$store_group2.id$'], 'store2'),
        DataFixture(
            CustomerAttribute::class,
            [
                'entity_type_id' => AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
                'used_in_forms' => ['customer_register_address'],
                'website_id' => '$website2.id$',
                'scope_is_visible' => 1,
                'is_visible' => 0,
            ],
            'attribute_1'
        ),
    ]
    public function testAttributesFormScope(): void
    {
        /** @var AttributeInterface $attribute1 */
        $attribute1 = DataFixtureStorageManager::getStorage()->get('attribute_1');

        $result = $this->graphQlQuery(sprintf(self::QUERY, 'customer_register_address'));

        $this->assertNotEmpty($result['attributesForm']['items']);
        $codes = $this->getAttributeCodes($result['attributesForm']['items']);

        $this->assertNotContains($attribute1->getAttributeCode(), $codes);

        /** @var StoreInterface $store */
        $store = DataFixtureStorageManager::getStorage()->get('store2');

        $result = $this->graphQlQuery(
            sprintf(self::QUERY, 'customer_register_address'),
            [],
            '',
            ['Store' => $store->getCode()]
        );

        $this->assertNotEmpty($result['attributesForm']['items']);
        $codes = $this->getAttributeCodes($result['attributesForm']['items']);
        $this->assertContains(
            $attribute1->getAttributeCode(),
            $codes,
            sprintf(
                "Attribute '%s' not found in query response in website scope",
                $attribute1->getAttributeCode()
            )
        );
    }

    /**
     * Retrieve an array of attribute codes based on an array of attributes data
     *
     * @param array $attributes
     * @return array
     */
    private function getAttributeCodes(array $attributes): array
    {
        return array_map(
            function (array $attribute) {
                return $attribute['code'];
            },
            $attributes
        );
    }
}
