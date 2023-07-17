<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogGraphQl;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\Catalog\Test\Fixture\CategoryAttribute;
use Magento\EavGraphQl\Model\Uid;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
#[
    DataFixture(
        CategoryAttribute::class,
        [
            'frontend_input' => 'multiselect',
            'is_filterable_in_search' => true,
            'position' => 4,
            'apply_to' => 'category'
        ],
        'category_attribute'
    ),
    DataFixture(
        Attribute::class,
        [
            'frontend_input' => 'multiselect',
            'is_filterable_in_search' => true,
            'position' => 5,
        ],
        'product_attribute'
    ),
]
class AttributesMetadataTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  customAttributeMetadataV2(attributes: [{attribute_code: "%s", entity_type: "%s"}]) {
    items {
      uid
      code
      label
      entity_type
      frontend_input
      is_required
      default_value
      is_unique
      ...on CatalogAttributeMetadata {
        is_filterable_in_search
        is_searchable
        is_filterable
        is_comparable
        is_html_allowed_on_front
        is_used_for_price_rules
        is_wysiwyg_enabled
        is_used_for_promo_rules
        used_in_product_listing
        apply_to
      }
    }
    errors {
      type
      message
    }
  }
}
QRY;

    /**
     * @return void
     * @throws \Exception
     */
    public function testMetadataProduct(): void
    {
        /** @var ProductAttributeInterface $productAttribute */
        $productAttribute = DataFixtureStorageManager::getStorage()->get('product_attribute');

        $productUid = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $productAttribute->getAttributeCode()
        );

        $result = $this->graphQlQuery(
            sprintf(
                self::QUERY,
                $productAttribute->getAttributeCode(),
                ProductAttributeInterface::ENTITY_TYPE_CODE
            )
        );

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'uid' => $productUid,
                            'code' => $productAttribute->getAttributeCode(),
                            'label' => $productAttribute->getDefaultFrontendLabel(),
                            'entity_type' => strtoupper(ProductAttributeInterface::ENTITY_TYPE_CODE),
                            'frontend_input' => 'MULTISELECT',
                            'is_required' => false,
                            'default_value' => $productAttribute->getDefaultValue(),
                            'is_unique' => false,
                            'is_filterable_in_search' => true,
                            'is_searchable' => false,
                            'is_filterable' => false,
                            'is_comparable' => false,
                            'is_html_allowed_on_front' => true,
                            'is_used_for_price_rules' => false,
                            'is_wysiwyg_enabled' => false,
                            'is_used_for_promo_rules' => false,
                            'used_in_product_listing' => false,
                            'apply_to' => null,
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testMetadataCategory(): void
    {
        /** @var CategoryAttributeInterface $categoryAttribute */
        $categoryAttribute = DataFixtureStorageManager::getStorage()->get('category_attribute');

        $categoryUid = Bootstrap::getObjectManager()->get(Uid::class)->encode(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $categoryAttribute->getAttributeCode()
        );

        $result = $this->graphQlQuery(
            sprintf(
                self::QUERY,
                $categoryAttribute->getAttributeCode(),
                CategoryAttributeInterface::ENTITY_TYPE_CODE
            )
        );

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
                            'uid' => $categoryUid,
                            'code' => $categoryAttribute->getAttributeCode(),
                            'label' => $categoryAttribute->getDefaultFrontendLabel(),
                            'entity_type' => strtoupper(CategoryAttributeInterface::ENTITY_TYPE_CODE),
                            'frontend_input' => 'MULTISELECT',
                            'is_required' => false,
                            'default_value' => $categoryAttribute->getDefaultValue(),
                            'is_unique' => false,
                            'is_filterable_in_search' => true,
                            'is_searchable' => false,
                            'is_filterable' => false,
                            'is_comparable' => false,
                            'is_html_allowed_on_front' => true,
                            'is_used_for_price_rules' => false,
                            'is_wysiwyg_enabled' => false,
                            'is_used_for_promo_rules' => false,
                            'used_in_product_listing' => false,
                            'apply_to' => ['CATEGORY'],
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
