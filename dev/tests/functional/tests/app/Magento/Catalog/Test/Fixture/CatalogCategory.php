<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CatalogCategory
 * Category fixture
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CatalogCategory extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Catalog\Test\Repository\CatalogCategory';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Catalog\Test\Handler\CatalogCategory\CatalogCategoryInterface';

    protected $defaultDataSet = [
        'name' => 'Category%isolation%',
        'path' => 'Default Category',
        'url_key' => 'category%isolation%',
        'is_active' => 'Yes',
        'include_in_menu' => 'Yes',
        'parent_id' => 2,
    ];

    protected $entity_id = [
        'attribute_code' => 'entity_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $entity_type_id = [
        'attribute_code' => 'entity_type_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $attribute_set_id = [
        'attribute_code' => 'attribute_set_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $description = [
        'attribute_code' => 'description',
        'backend_type' => 'text',
        'is_required' => '0',
        'default_value' => '',
        'input' => 'textarea',
    ];

    protected $parent_id = [
        'attribute_code' => 'parent_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => null,
        'source' => 'Magento\Catalog\Test\Fixture\CatalogCategory\ParentId',
    ];

    protected $created_at = [
        'attribute_code' => 'created_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $updated_at = [
        'attribute_code' => 'updated_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $path = [
        'attribute_code' => 'path',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'group' => null,
        'input' => '',
    ];

    protected $position = [
        'attribute_code' => 'position',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $level = [
        'attribute_code' => 'level',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $children_count = [
        'attribute_code' => 'children_count',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $available_product_listing_config = [
        'attribute_code' => 'available_product_listing_config',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'group' => 'display_setting',
        'input' => 'checkbox',
    ];

    protected $available_sort_by = [
        'attribute_code' => 'available_sort_by',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'display_setting',
        'input' => 'multiselect',
    ];

    protected $default_product_listing_config = [
        'attribute_code' => 'default_product_listing_config',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'display_setting',
        'input' => 'checkbox',
    ];

    protected $default_sort_by = [
        'attribute_code' => 'default_sort_by',
        'backend_type' => 'varchar',
        'is_required' => '0',
        'default_value' => '',
        'group' => 'display_setting',
        'input' => 'select',
    ];

    protected $meta_title = [
        'attribute_code' => 'meta_title',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
        'group' => null,
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'virtual',
        'group' => 'general_information',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'virtual',
        'group' => 'general_information',
    ];

    protected $is_anchor = [
        'attribute_code' => 'is_anchor',
        'backend_type' => 'virtual',
        'group' => 'general_information',
    ];

    protected $url_key = [
        'attribute_code' => 'url_key',
        'backend_type' => 'virtual',
        'group' => 'general_information',
    ];

    protected $include_in_menu = [
        'attribute_code' => 'include_in_menu',
        'backend_type' => 'virtual',
        'group' => 'general_information',
    ];

    protected $landing_page = [
        'attribute_code' => 'landing_page',
        'backend_type' => 'virtual',
        'input' => 'select',
        'group' => 'display_setting',
    ];

    protected $display_mode = [
        'attribute_code' => 'display_mode',
        'backend_type' => 'virtual',
        'input' => 'select',
        'group' => 'display_setting',
    ];

    protected $category_products = [
        'attribute_code' => 'category_products',
        'backend_type' => 'virtual',
        'group' => 'category_products',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogCategory\CategoryProducts',
    ];

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function getEntityTypeId()
    {
        return $this->getData('entity_type_id');
    }

    public function getAttributeSetId()
    {
        return $this->getData('attribute_set_id');
    }

    public function getDescription()
    {
        return $this->getData('description');
    }

    public function getParentId()
    {
        return $this->getData('parent_id');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

    public function getPath()
    {
        return $this->getData('path');
    }

    public function getPosition()
    {
        return $this->getData('position');
    }

    public function getLevel()
    {
        return $this->getData('level');
    }

    public function getChildrenCount()
    {
        return $this->getData('children_count');
    }

    public function getAvailableProductListingConfig()
    {
        return $this->getData('available_product_listing_config');
    }

    public function getAvailableSortBy()
    {
        return $this->getData('available_sort_by');
    }

    public function getDefaultProductListingConfig()
    {
        return $this->getData('default_product_listing_config');
    }

    public function getDefaultSortBy()
    {
        return $this->getData('default_sort_by');
    }

    public function getMetaTitle()
    {
        return $this->getData('meta_title');
    }

    public function getId()
    {
        return $this->getData('id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getIsAnchor()
    {
        return $this->getData('is_anchor');
    }

    public function getUrlKey()
    {
        return $this->getData('url_key');
    }

    public function getIncludeInMenu()
    {
        return $this->getData('include_in_menu');
    }

    public function getLandingPage()
    {
        return $this->getData('landing_page');
    }

    public function getDisplayMode()
    {
        return $this->getData('display_mode');
    }

    public function getCategoryProducts()
    {
        return $this->getData('category_products');
    }

    public function getBlockId()
    {
        return $this->getData('block_id');
    }
}
