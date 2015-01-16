<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

interface EavAttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    const IS_WYSIWYG_ENABLED = 'is_wysiwyg_enabled';

    const IS_HTML_ALLOWED_ON_FRONT = 'is_html_allowed_on_front';

    const USED_FOR_SORT_BY = 'used_for_sort_by';

    const IS_FILTERABLE = 'is_filterable';

    const IS_FILTERABLE_IN_SEARCH = 'is_filterable_in_search';

    const POSITION = 'position';

    const APPLY_TO = 'apply_to';

    const IS_CONFIGURABLE = 'is_configurable';

    const IS_SEARCHABLE = 'is_searchable';

    const IS_VISIBLE_IN_ADVANCED_SEARCH = 'is_visible_in_advanced_search';

    const IS_COMPARABLE = 'is_comparable';

    const IS_USED_FOR_PROMO_RULES = 'is_used_for_promo_rules';

    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';

    const USED_IN_PRODUCT_LISTING = 'used_in_product_listing';

    const IS_VISIBLE = 'is_visible';

    /**
     * Enable WYSIWYG flag
     *
     * @return bool|null
     */
    public function getIsWysiwygEnabled();

    /**
     * Whether the HTML tags are allowed on the frontend
     *
     * @return bool|null
     */
    public function getIsHtmlAllowedOnFront();

    /**
     * Whether it is used for sorting in product listing
     *
     * @return bool|null
     */
    public function getUsedForSortBy();
    /**
     * Whether it used in layered navigation
     *
     * @return bool|null
     */
    public function getIsFilterable();

    /**
     * Whether it is used in search results layered navigation
     *
     * @return bool|null
     */
    public function getIsFilterableInSearch();

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Get apply to value for the element
     *
     * Apply to. Empty for "Apply to all"
     * or array of the following possible values:
     *  - 'simple',
     *  - 'grouped',
     *  - 'configurable',
     *  - 'virtual',
     *  - 'bundle',
     *  - 'downloadable'
     *
     * @return string[]|null
     */
    public function getApplyTo();

    /**
     * Whether the attribute can be used for configurable products
     *
     * @return string|null
     */
    public function getIsConfigurable();
    /**
     * Whether the attribute can be used in Quick Search
     *
     * @return string|null
     */
    public function getIsSearchable();
    /**
     * Whether the attribute can be used in Advanced Search
     *
     * @return string|null
     */
    public function getIsVisibleInAdvancedSearch();

    /**
     * Whether the attribute can be compared on the frontend
     *
     * @return string|null
     */
    public function getIsComparable();

    /**
     * Whether the attribute can be used for promo rules
     *
     * @return string|null
     */
    public function getIsUsedForPromoRules();
    /**
     * Whether the attribute is visible on the frontend
     *
     * @return string|null
     */
    public function getIsVisibleOnFront();
    /**
     * Whether the attribute can be used in product listing
     *
     * @return string|null
     */
    public function getUsedInProductListing();

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool|null
     */
    public function getIsVisible();

    /**
     * Retrieve attribute scope
     *
     * @return string|null
     */
    public function getScope();
}
