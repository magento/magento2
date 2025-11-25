<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\TestFramework\Helper\Bootstrap;

class TypeLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypeLocator
     */
    private $attributeTypeLocator;

    protected function setUp(): void
    {
        $this->attributeTypeLocator = Bootstrap::getObjectManager()->get(TypeLocator::class);
    }

    /**
     * @param string $entityType
     * @param string[] $attributeList
     * @dataProvider getExpectedAttributeTypesProvider
     */
    public function testGetType(
        $entityType,
        array $attributeList
    ) {
        foreach ($attributeList as $attributeCode => $expectedType) {
            $this->assertEquals(
                $expectedType,
                $this->attributeTypeLocator->getType($attributeCode, $entityType),
                "Expected type of '{$attributeCode}' product attribute was '{$expectedType}"
            );
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function getExpectedAttributeTypesProvider(): array
    {
        return [
            'product' => [
                'catalog_product',
                [
                    'name' => 'string',
                    'sku' => 'string',
                    'description' => 'string',
                    'short_description' => 'string',
                    'price' => 'double',
                    'special_price' => 'double',
                    'special_from_date' => 'string',
                    'special_to_date' => 'string',
                    'cost' => 'double',
                    'weight' => 'double',
                    'manufacturer' => 'int',
                    'meta_title' => 'string',
                    'meta_keyword' => 'string',
                    'meta_description' => 'string',
                    'image' => 'string',
                    'small_image' => 'string',
                    'thumbnail' => 'string',
                    'old_id' => 'int',
                    'tier_price' => 'double',
                    'color' => 'int',
                    'news_from_date' => 'string',
                    'news_to_date' => 'string',
                    'gallery' => 'string',
                    'status' => 'int',
                    'minimal_price' => 'double',
                    'visibility' => 'int',
                    'custom_design' => 'string',
                    'custom_design_from' => 'string',
                    'custom_design_to' => 'string',
                    'custom_layout_update' => 'string',
                    'page_layout' => 'string',
                    'category_ids' => 'int[]',
                    'options_container' => 'string',
                    'required_options' => 'string',
                    'has_options' => 'string',
                    'image_label' => 'string',
                    'small_image_label' => 'string',
                    'thumbnail_label' => 'string',
                    'created_at' => 'string',
                    'updated_at' => 'string',
                    'country_of_manufacture' => 'string',
                    'quantity_and_stock_status' => \Magento\CatalogInventory\Api\Data\StockItemInterface::class . '[]',
                    'custom_layout' => 'string',
                    'url_key' => 'string',
                    'url_path' => 'string',
                    'msrp' => 'double',
                    'msrp_display_actual_price_type' => 'string',
                    'price_type' => 'int',
                    'sku_type' => 'int',
                    'weight_type' => 'int',
                    'price_view' => 'int',
                    'shipment_type' => 'int',
                    'links_purchased_separately' => 'int',
                    'samples_title' => 'string',
                    'links_title' => 'string',
                    'links_exist' => 'int',
                    'gift_message_available' => 'string',
                    'swatch_image' => 'string',
                    'tax_class_id' => 'int'
                ]
            ],
            'customer'=> [
                'customer',
                [
                    'confirmation' => 'string',
                    'created_at' => 'string',
                    'website_id' => 'int',
                    'store_id' => 'int',
                    'created_in' => 'string',
                    'group_id' => 'string',
                    'disable_auto_group_change' => 'boolean',
                    'prefix' => 'string',
                    'firstname' => 'string',
                    'middlename' => 'string',
                    'lastname' => 'string',
                    'suffix' => 'string',
                    'email' => 'string',
                    'password_hash' => 'string',
                    'default_billing' => \Magento\Customer\Api\Data\AddressInterface::class,
                    'default_shipping' => \Magento\Customer\Api\Data\AddressInterface::class,
                    'updated_at' => 'string',
                    'dob' => 'string',
                    'taxvat' => 'string',
                    'failures_num' => 'string',
                    'gender' => 'string',
                    'first_failure' => 'string',
                    'rp_token' => 'string',
                    'rp_token_created_at' => 'string',
                    'lock_expires' => 'string',
                ]
            ],
            'customer address' => [
                'customer_address',
                [
                    'prefix' => 'string',
                    'firstname' => 'string',
                    'middlename' => 'string',
                    'lastname' => 'string',
                    'suffix' => 'string',
                    'company' => 'string',
                    'street' => 'string',
                    'city' => 'string',
                    'country_id' => 'string',
                    'region' => \Magento\Customer\Api\Data\RegionInterface::class,
                    'region_id' => 'string',
                    'postcode' => 'string',
                    'telephone' => 'string',
                    'fax' => 'string',
                    'vat_is_valid' => 'string',
                    'vat_request_id' => 'string',
                    'vat_request_date' => 'string',
                    'vat_request_success' => 'string',
                    'vat_id' => 'string',
                ]
            ],
            'category' => [
                'catalog_category',
                [
                    'name' => 'string',
                    'is_active' => 'int',
                    'url_key' => 'string',
                    'description' => 'string',
                    'image' => 'string',
                    'custom_use_parent_settings' => 'int',
                    'meta_title' => 'string',
                    'custom_apply_to_products' => 'int',
                    'meta_keywords' => 'string',
                    'meta_description' => 'string',
                    'display_mode' => 'string',
                    'custom_design' => 'string',
                    'include_in_menu' => 'int',
                    'path' => 'string',
                    'position' => 'string',
                    'all_children' => 'string',
                    'path_in_store' => 'string',
                    'children' => 'string',
                    'url_path' => 'string',
                    'landing_page' => 'int',
                    'level' => 'string',
                    'children_count' => 'string',
                    'is_anchor' => 'int',
                    'custom_design_from' => 'string',
                    'custom_design_to' => 'string',
                    'available_sort_by' => 'string[]',
                    'page_layout' => 'string',
                    'default_sort_by' => 'string',
                    'filter_price_range' => 'double',
                    'custom_layout_update' => 'string',
                ]
            ],
            'undefined attributes' => [
                'catalog_product',
                ['media_gallery' => 'anyType', 'undefine_attribute' => 'anyType']
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
