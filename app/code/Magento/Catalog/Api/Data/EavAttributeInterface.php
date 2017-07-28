<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface EavAttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    const IS_WYSIWYG_ENABLED = 'is_wysiwyg_enabled';

    const IS_HTML_ALLOWED_ON_FRONT = 'is_html_allowed_on_front';

    const USED_FOR_SORT_BY = 'used_for_sort_by';

    const IS_FILTERABLE = 'is_filterable';

    const IS_FILTERABLE_IN_SEARCH = 'is_filterable_in_search';

    const IS_USED_IN_GRID = 'is_used_in_grid';

    const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';

    const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';

    const POSITION = 'position';

    const APPLY_TO = 'apply_to';

    const IS_SEARCHABLE = 'is_searchable';

    const IS_VISIBLE_IN_ADVANCED_SEARCH = 'is_visible_in_advanced_search';

    const IS_COMPARABLE = 'is_comparable';

    const IS_USED_FOR_PROMO_RULES = 'is_used_for_promo_rules';

    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';

    const USED_IN_PRODUCT_LISTING = 'used_in_product_listing';

    const IS_VISIBLE = 'is_visible';

    const SCOPE_STORE_TEXT = 'store';

    const SCOPE_GLOBAL_TEXT = 'global';

    const SCOPE_WEBSITE_TEXT = 'website';

    /**
     * Enable WYSIWYG flag
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsWysiwygEnabled();

    /**
     * Set whether WYSIWYG is enabled flag
     *
     * @param bool $isWysiwygEnabled
     * @return $this
     * @since 2.0.0
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled);

    /**
     * Whether the HTML tags are allowed on the frontend
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsHtmlAllowedOnFront();

    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param bool $isHtmlAllowedOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront);

    /**
     * Whether it is used for sorting in product listing
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getUsedForSortBy();

    /**
     * Set whether it is used for sorting in product listing
     *
     * @param bool $usedForSortBy
     * @return $this
     * @since 2.0.0
     */
    public function setUsedForSortBy($usedForSortBy);

    /**
     * Whether it used in layered navigation
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsFilterable();

    /**
     * Set whether it used in layered navigation
     *
     * @param bool $isFilterable
     * @return $this
     * @since 2.0.0
     */
    public function setIsFilterable($isFilterable);

    /**
     * Whether it is used in search results layered navigation
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsFilterableInSearch();

    /**
     * Whether it is used in catalog product grid
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsUsedInGrid();

    /**
     * Whether it is visible in catalog product grid
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsVisibleInGrid();

    /**
     * Whether it is filterable in catalog product grid
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsFilterableInGrid();

    /**
     * Set is attribute used in grid
     *
     * @param bool|null $isUsedInGrid
     * @return $this
     * @since 2.2.0
     */
    public function setIsUsedInGrid($isUsedInGrid);

    /**
     * Set is attribute visible in grid
     *
     * @param bool|null $isVisibleInGrid
     * @return $this
     * @since 2.2.0
     */
    public function setIsVisibleInGrid($isVisibleInGrid);

    /**
     * Set is attribute filterable in grid
     *
     * @param bool|null $isFilterableInGrid
     * @return $this
     * @since 2.2.0
     */
    public function setIsFilterableInGrid($isFilterableInGrid);

    /**
     * Set whether it is used in search results layered navigation
     *
     * @param bool $isFilterableInSearch
     * @return $this
     * @since 2.0.0
     */
    public function setIsFilterableInSearch($isFilterableInSearch);

    /**
     * Get position
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

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
     * @since 2.0.0
     */
    public function getApplyTo();

    /**
     * Set apply to value for the element
     *
     * @param string[]|string $applyTo
     * @return $this
     * @since 2.0.0
     */
    public function setApplyTo($applyTo);

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getIsSearchable();

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @param string $isSearchable
     * @return $this
     * @since 2.0.0
     */
    public function setIsSearchable($isSearchable);

    /**
     * Whether the attribute can be used in Advanced Search
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getIsVisibleInAdvancedSearch();

    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param string $isVisibleInAdvancedSearch
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch);

    /**
     * Whether the attribute can be compared on the frontend
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getIsComparable();

    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param string $isComparable
     * @return $this
     * @since 2.0.0
     */
    public function setIsComparable($isComparable);

    /**
     * Whether the attribute can be used for promo rules
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getIsUsedForPromoRules();

    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param string $isUsedForPromoRules
     * @return $this
     * @since 2.0.0
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules);

    /**
     * Whether the attribute is visible on the frontend
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getIsVisibleOnFront();

    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param string $isVisibleOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Whether the attribute can be used in product listing
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getUsedInProductListing();

    /**
     * Set whether the attribute can be used in product listing
     *
     * @param string $usedInProductListing
     * @return $this
     * @since 2.0.0
     */
    public function setUsedInProductListing($usedInProductListing);

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getIsVisible();

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisible($isVisible);

    /**
     * Retrieve attribute scope
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getScope();

    /**
     * Set attribute scope
     *
     * @param string $scope
     * @return $this
     * @since 2.0.0
     */
    public function setScope($scope);

    /**
     * @return \Magento\Catalog\Api\Data\EavAttributeExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();
}
