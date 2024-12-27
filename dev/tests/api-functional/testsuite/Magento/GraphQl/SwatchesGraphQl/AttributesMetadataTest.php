<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SwatchesGraphQl;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Test\Fixture\Attribute;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test catalog EAV attributes metadata retrieval via GraphQL API
 */
#[
    DataFixture(
        Attribute::class,
        [
            'frontend_input' => 'multiselect',
            'is_filterable_in_search' => true,
            'position' => 6,
            'additional_data' =>
                '{"swatch_input_type":"visual","update_product_preview_image":1,"use_product_image_for_swatch":0}'
        ],
        'product_attribute'
    ),
]
class AttributesMetadataTest extends GraphQlAbstract
{
    private const QUERY = <<<QRY
{
  customAttributeMetadataV2(attributes: [{attribute_code: "%s", entity_type: "catalog_product"}]) {
    items {
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
        swatch_input_type
        update_product_preview_image
        use_product_image_for_swatch
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

        $result = $this->graphQlQuery(
            sprintf(
                self::QUERY,
                $productAttribute->getAttributeCode()
            )
        );

        $this->assertEquals(
            [
                'customAttributeMetadataV2' => [
                    'items' => [
                        [
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
                            'swatch_input_type' => 'VISUAL',
                            'update_product_preview_image' => true,
                            'use_product_image_for_swatch' => false
                        ]
                    ],
                    'errors' => []
                ]
            ],
            $result
        );
    }
}
