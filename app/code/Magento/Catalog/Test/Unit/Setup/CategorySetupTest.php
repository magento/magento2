<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Setup;

class CategorySetupTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Setup\CategorySetup */
    protected $unit;

    protected function setUp()
    {
        $this->unit = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            'Magento\Catalog\Setup\CategorySetup'
        );
    }

    public function testGetDefaultEntitiesContainAllAttributes()
    {
        $defaultEntities = $this->unit->getDefaultEntities();

        $this->assertEquals(
            [
                'name',
                'is_active',
                'description',
                'image',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'display_mode',
                'landing_page',
                'is_anchor',
                'path',
                'position',
                'all_children',
                'path_in_store',
                'children',
                'custom_design',
                'custom_design_from',
                'custom_design_to',
                'page_layout',
                'custom_layout_update',
                'level',
                'children_count',
                'available_sort_by',
                'default_sort_by',
                'include_in_menu',
                'custom_use_parent_settings',
                'custom_apply_to_products',
                'filter_price_range',
            ],
            array_keys($defaultEntities['catalog_category']['attributes'])
        );

        $this->assertEquals(
            [
                'name',
                'sku',
                'description',
                'short_description',
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'cost',
                'weight',
                'manufacturer',
                'meta_title',
                'meta_keyword',
                'meta_description',
                'image',
                'small_image',
                'thumbnail',
                'media_gallery',
                'old_id',
                'tier_price',
                'color',
                'news_from_date',
                'news_to_date',
                'gallery',
                'status',
                'minimal_price',
                'visibility',
                'custom_design',
                'custom_design_from',
                'custom_design_to',
                'custom_layout_update',
                'page_layout',
                'category_ids',
                'options_container',
                'required_options',
                'has_options',
                'image_label',
                'small_image_label',
                'thumbnail_label',
                'created_at',
                'updated_at',
                'country_of_manufacture',
                'quantity_and_stock_status',
            ],
            array_keys($defaultEntities['catalog_product']['attributes'])
        );
    }
}
