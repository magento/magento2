<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CatalogAttributeEntity
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CatalogProductAttribute extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Catalog\Test\Repository\CatalogProductAttribute';

    // @codingStandardsIgnoreStart
    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Catalog\Test\Handler\CatalogProductAttribute\CatalogProductAttributeInterface';
    // @codingStandardsIgnoreEnd

    protected $defaultDataSet = [
        'frontend_label' => 'attribute_label%isolation%',
        'frontend_input' => 'Text Field',
        'is_required' => 'No',
    ];

    protected $attribute_id = [
        'attribute_code' => 'attribute_id',
        'backend_type' => 'smallint',
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

    protected $attribute_code = [
        'attribute_code' => 'attribute_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'advanced-properties',
    ];

    protected $attribute_model = [
        'attribute_code' => 'attribute_model',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $backend_model = [
        'attribute_code' => 'backend_model',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $backend_type = [
        'attribute_code' => 'backend_type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'static',
        'input' => '',
    ];

    protected $backend_table = [
        'attribute_code' => 'backend_table',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $frontend_model = [
        'attribute_code' => 'frontend_model',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $frontend_input = [
        'attribute_code' => 'frontend_input',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => 'select',
        'group' => 'properties',
    ];

    protected $frontend_label = [
        'attribute_code' => 'frontend_label',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'properties',
    ];

    protected $manage_frontend_label = [
        'attribute_code' => 'manage_frontend_label',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'manage-labels',
    ];

    protected $frontend_class = [
        'attribute_code' => 'frontend_class',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $source_model = [
        'attribute_code' => 'source_model',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_required = [
        'attribute_code' => 'is_required',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => 'select',
        'group' => 'properties',
    ];

    protected $is_user_defined = [
        'attribute_code' => 'is_user_defined',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_unique = [
        'attribute_code' => 'is_unique',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'advanced-properties',
    ];

    protected $note = [
        'attribute_code' => 'note',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $frontend_input_renderer = [
        'attribute_code' => 'frontend_input_renderer',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_global = [
        'attribute_code' => 'is_global',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
        'group' => 'advanced-properties',
    ];

    protected $is_visible = [
        'attribute_code' => 'is_visible',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $is_searchable = [
        'attribute_code' => 'is_searchable',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_filterable = [
        'attribute_code' => 'is_filterable',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_comparable = [
        'attribute_code' => 'is_comparable',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_visible_on_front = [
        'attribute_code' => 'is_visible_on_front',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_html_allowed_on_front = [
        'attribute_code' => 'is_html_allowed_on_front',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_used_for_price_rules = [
        'attribute_code' => 'is_used_for_price_rules',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $is_filterable_in_search = [
        'attribute_code' => 'is_filterable_in_search',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $used_in_product_listing = [
        'attribute_code' => 'used_in_product_listing',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $used_for_sort_by = [
        'attribute_code' => 'used_for_sort_by',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $apply_to = [
        'attribute_code' => 'apply_to',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_visible_in_advanced_search = [
        'attribute_code' => 'is_visible_in_advanced_search',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'frontend-properties',
    ];

    protected $position = [
        'attribute_code' => 'position',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_wysiwyg_enabled = [
        'attribute_code' => 'is_wysiwyg_enabled',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_used_for_promo_rules = [
        'attribute_code' => 'is_used_for_promo_rules',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_configurable = [
        'attribute_code' => 'is_configurable',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'advanced-properties',
    ];

    protected $search_weight = [
        'attribute_code' => 'search_weight',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $options = [
        'attribute_code' => 'options',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'source' => 'Magento\Catalog\Test\Fixture\CatalogProductAttribute\Options',
    ];

    protected $default_value_text = [
        'attribute_code' => 'default_value_text',
        'backend_type' => 'text',
        'group' => 'advanced-properties',
    ];

    protected $default_value_textarea = [
        'attribute_code' => 'default_value_textarea',
        'backend_type' => 'text',
        'group' => 'advanced-properties',
    ];

    protected $default_value_date = [
        'attribute_code' => 'default_value_date',
        'backend_type' => 'text',
        'group' => 'advanced-properties',
        'source' => 'Magento\Backend\Test\Fixture\Date',
    ];

    protected $default_value_yesno = [
        'attribute_code' => 'default_value_yesno',
        'backend_type' => 'text',
        'group' => 'advanced-properties',
    ];

    public function getDefaultValueText()
    {
        return $this->getData('default_value_text');
    }

    public function getDefaultValueTextarea()
    {
        return $this->getData('default_value_textarea');
    }

    public function getDefaultValueDate()
    {
        return $this->getData('default_value_date');
    }

    public function getDefaultValueYesno()
    {
        return $this->getData('default_value_yesno');
    }

    public function getAttributeId()
    {
        return $this->getData('attribute_id');
    }

    public function getEntityTypeId()
    {
        return $this->getData('entity_type_id');
    }

    public function getAttributeCode()
    {
        return $this->getData('attribute_code');
    }

    public function getAttributeModel()
    {
        return $this->getData('attribute_model');
    }

    public function getBackendModel()
    {
        return $this->getData('backend_model');
    }

    public function getBackendType()
    {
        return $this->getData('backend_type');
    }

    public function getBackendTable()
    {
        return $this->getData('backend_table');
    }

    public function getFrontendModel()
    {
        return $this->getData('frontend_model');
    }

    public function getFrontendInput()
    {
        return $this->getData('frontend_input');
    }

    public function getFrontendLabel()
    {
        return $this->getData('frontend_label');
    }

    public function getManageFrontendLabel()
    {
        return $this->getData('manage_frontend_label');
    }

    public function getFrontendClass()
    {
        return $this->getData('frontend_class');
    }

    public function getSourceModel()
    {
        return $this->getData('source_model');
    }

    public function getIsRequired()
    {
        return $this->getData('is_required');
    }

    public function getIsUserDefined()
    {
        return $this->getData('is_user_defined');
    }

    public function getIsUnique()
    {
        return $this->getData('is_unique');
    }

    public function getNote()
    {
        return $this->getData('note');
    }

    public function getFrontendInputRenderer()
    {
        return $this->getData('frontend_input_renderer');
    }

    public function getIsGlobal()
    {
        return $this->getData('is_global');
    }

    public function getIsVisible()
    {
        return $this->getData('is_visible');
    }

    public function getIsSearchable()
    {
        return $this->getData('is_searchable');
    }

    public function getIsFilterable()
    {
        return $this->getData('is_filterable');
    }

    public function getIsComparable()
    {
        return $this->getData('is_comparable');
    }

    public function getIsVisibleOnFront()
    {
        return $this->getData('is_visible_on_front');
    }

    public function getIsHtmlAllowedOnFront()
    {
        return $this->getData('is_html_allowed_on_front');
    }

    public function getIsUsedForPriceRules()
    {
        return $this->getData('is_used_for_price_rules');
    }

    public function getIsFilterableInSearch()
    {
        return $this->getData('is_filterable_in_search');
    }

    public function getUsedInProductListing()
    {
        return $this->getData('used_in_product_listing');
    }

    public function getUsedForSortBy()
    {
        return $this->getData('used_for_sort_by');
    }

    public function getApplyTo()
    {
        return $this->getData('apply_to');
    }

    public function getIsVisibleInAdvancedSearch()
    {
        return $this->getData('is_visible_in_advanced_search');
    }

    public function getPosition()
    {
        return $this->getData('position');
    }

    public function getIsWysiwygEnabled()
    {
        return $this->getData('is_wysiwyg_enabled');
    }

    public function getIsUsedForPromoRules()
    {
        return $this->getData('is_used_for_promo_rules');
    }

    public function getIsConfigurable()
    {
        return $this->getData('is_configurable');
    }

    public function getSearchWeight()
    {
        return $this->getData('search_weight');
    }

    public function getOptions()
    {
        return $this->getData('options');
    }
}
