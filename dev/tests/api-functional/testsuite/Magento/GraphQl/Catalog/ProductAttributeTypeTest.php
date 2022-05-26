<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Catalog;

use Magento\TestFramework\TestCase\GraphQlAbstract;

class ProductAttributeTypeTest extends GraphQlAbstract
{
    /**
     * Verify the schema returns correct attribute type , given the attributeCode and corresponding entityType
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAttributeTypeResolver()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"description",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"status",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"special_price",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"disable_auto_group_change",
      entity_type:"customer"
    }
    {
      attribute_code:"special_price",
      entity_type:"Magento\\\\Catalog\\\\Api\\\\Data\\\\ProductInterface"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type
      input_type
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = [
            'description',
            'status',
            'special_price',
            'disable_auto_group_change',
            'special_price'
        ];
        $entityType = [
            'catalog_product',
            'catalog_product',
            'catalog_product',
            'customer',
            \Magento\Catalog\Api\Data\ProductInterface::class
        ];
        $attributeTypes = ['String', 'Int', 'Float','Boolean', 'Float'];
        $inputTypes = ['textarea', 'select', 'price', 'boolean', 'price'];

        $this->assertAttributeType($attributeTypes, $expectedAttributeCodes, $entityType, $inputTypes, $response);
    }

    /**
     * Verify that the complex EAV attributes types are resolved
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testComplexAttributeTypeResolver()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"default_sort_by",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"available_sort_by",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"store_id",
      entity_type:"customer"
    },
    {
      attribute_code:"quantity_and_stock_status",
      entity_type:"catalog_product"
    },
    {
      attribute_code:"default_billing",
      entity_type:"customer"
    },
    {
     attribute_code:"region"
     entity_type:"customer_address"
    },
    {
      attribute_code:"media_gallery",
      entity_type:"catalog_product"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type
      input_type
      storefront_properties {
         use_in_product_listing
         use_in_layered_navigation
         use_in_search_results_layered_navigation
         visible_on_catalog_pages
      }
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = [
            'default_sort_by',
            'available_sort_by',
            'store_id',
            'quantity_and_stock_status',
            'default_billing',
            'region',
            'media_gallery'
        ];
        $entityTypes = [
            'catalog_category',
            'catalog_category',
            'customer',
            'catalog_product',
            'customer',
            'customer_address',
            'catalog_product'
        ];
        $attributeTypes = [
            'String[]',
            'String[]',
            'Int',
            'CatalogInventoryDataStockItemInterface[]',
            'CustomerDataAddressInterface',
            'CustomerDataRegionInterface',
            'ProductMediaGallery'
        ];
        $inputTypes = [
            'select',
            'multiselect',
            'select',
            'select',
            'text',
            'text',
            'gallery'
        ];
        $this->assertComplexAttributeType(
            $attributeTypes,
            $expectedAttributeCodes,
            $entityTypes,
            $inputTypes,
            $response
        );
    }

    /**
     * Verify the schema returns attribute type as AnyType
     *
     * For undefined attributes and for attributes with no backendModel mapping available
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testUnDefinedAttributeType()
    {
        $query
            = <<<QUERY
{
  customAttributeMetadata(attributes:
  [
    {
      attribute_code:"undefine_attribute",
      entity_type:"catalog_category"
    },
    {
      attribute_code:"special_price",
      entity_type:"customer"
    }
  ]
  )
  {
    items
    {
      attribute_code
      attribute_type
      entity_type
    }
  }
 }
QUERY;
        $response = $this->graphQlQuery($query);
        $expectedAttributeCodes = ['undefine_attribute', 'special_price'];
        $entityTypes = ['catalog_category', 'customer'];
        $attributeTypes = ['AnyType'];
        $attributeMetaData = array_map(null, $response['customAttributeMetadata']['items'], $entityTypes);
        foreach ($attributeMetaData as $itemsIndex => $itemArray) {
            $this->assertResponseFields(
                $attributeMetaData[$itemsIndex][0],
                [
                  "attribute_code" => $expectedAttributeCodes[$itemsIndex],
                  "attribute_type" =>$attributeTypes[0],
                  "entity_type" => $entityTypes[$itemsIndex]
                ]
            );
        }
    }

    /**
     * @param array $attributeTypes
     * @param array $expectedAttributeCodes
     * @param array $entityTypes
     * @param array $inputTypes
     * @param array $actualResponse
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function assertComplexAttributeType(
        $attributeTypes,
        $expectedAttributeCodes,
        $entityTypes,
        $inputTypes,
        $actualResponse
    ) {
        $attributeMetaDataItems = array_map(null, $actualResponse['customAttributeMetadata']['items'], $attributeTypes);

        foreach ($attributeMetaDataItems as $itemIndex => $itemArray) {
            if ($itemArray[0]['entity_type'] === 'catalog_category'
                || $itemArray[0]['entity_type'] ==='catalog_product') {
                $this->assertResponseFields(
                    $attributeMetaDataItems[$itemIndex][0],
                    [
                        "attribute_code" => $expectedAttributeCodes[$itemIndex],
                        "attribute_type" => $attributeTypes[$itemIndex],
                        "entity_type" => $entityTypes[$itemIndex],
                        "input_type" => $inputTypes[$itemIndex],
                        "storefront_properties" => [
                            'use_in_product_listing' => false,
                            'use_in_layered_navigation' => 'NO',
                            'use_in_search_results_layered_navigation' => false,
                            'visible_on_catalog_pages' => false,
                        ]
                    ]
                );
            } else {
                $this->assertNotEmpty($attributeMetaDataItems[$itemIndex][0]['storefront_properties']);
                // 5 fields are present
                $this->assertCount(4, $attributeMetaDataItems[$itemIndex][0]['storefront_properties']);
                unset($attributeMetaDataItems[$itemIndex][0]['storefront_properties']);
                $this->assertResponseFields(
                    $attributeMetaDataItems[$itemIndex][0],
                    [
                        "attribute_code" => $expectedAttributeCodes[$itemIndex],
                        "attribute_type" => $attributeTypes[$itemIndex],
                        "entity_type" => $entityTypes[$itemIndex],
                        "input_type" => $inputTypes[$itemIndex]
                    ]
                );

            }

        }
    }

    /**
     * @param array $attributeTypes
     * @param array $expectedAttributeCodes
     * @param array $entityTypes
     * @param array $inputTypes
     * @param array $actualResponse
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function assertAttributeType(
        $attributeTypes,
        $expectedAttributeCodes,
        $entityTypes,
        $inputTypes,
        $actualResponse
    ) {
        $attributeMetaDataItems = array_map(null, $actualResponse['customAttributeMetadata']['items'], $attributeTypes);

        foreach ($attributeMetaDataItems as $itemIndex => $itemArray) {
                $this->assertResponseFields(
                    $attributeMetaDataItems[$itemIndex][0],
                    [
                        "attribute_code" => $expectedAttributeCodes[$itemIndex],
                        "attribute_type" => $attributeTypes[$itemIndex],
                        "entity_type" => $entityTypes[$itemIndex],
                        "input_type" => $inputTypes[$itemIndex]
                    ]
                );
        }
    }
}
