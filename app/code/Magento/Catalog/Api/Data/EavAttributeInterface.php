<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
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
     */
    public function getIsWysiwygEnabled();

    /**
     * Set whether WYSIWYG is enabled flag
     *
     * @param bool $isWysiwygEnabled
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled);

    /**
     * Whether the HTML tags are allowed on the frontend
     *
     * @return bool|null
     */
    public function getIsHtmlAllowedOnFront();

    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param bool $isHtmlAllowedOnFront
     * @return $this
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront);

    /**
     * Whether it is used for sorting in product listing
     *
     * @return bool|null
     */
    public function getUsedForSortBy();

    /**
     * Set whether it is used for sorting in product listing
     *
     * @param bool $usedForSortBy
     * @return $this
     */
    public function setUsedForSortBy($usedForSortBy);

    /**
     * Whether it used in layered navigation
     *
     * @return bool|null
     */
    public function getIsFilterable();

    /**
     * Set whether it used in layered navigation
     *
     * @param bool $isFilterable
     * @return $this
     */
    public function setIsFilterable($isFilterable);

    /**
     * Whether it is used in search results layered navigation
     *
     * @return bool|null
     */
    public function getIsFilterableInSearch();

    /**
     * Whether it is used in catalog product grid
     *
     * @return bool|null
     */
    public function getIsUsedInGrid();

    /**
     * Whether it is visible in catalog product grid
     *
     * @return bool|null
     */
    public function getIsVisibleInGrid();

    /**
     * Whether it is filterable in catalog product grid
     *
     * @return bool|null
     */
    public function getIsFilterableInGrid();

    /**
     * Set is attribute used in grid
     *
     * @param bool|null $isUsedInGrid
     * @return $this
     */
    public function setIsUsedInGrid($isUsedInGrid);

    /**
     * Set is attribute visible in grid
     *
     * @param bool|null $isVisibleInGrid
     * @return $this
     */
    public function setIsVisibleInGrid($isVisibleInGrid);

    /**
     * Set is attribute filterable in grid
     *
     * @param bool|null $isFilterableInGrid
     * @return $this
     */
    public function setIsFilterableInGrid($isFilterableInGrid);

    /**
     * Set whether it is used in search results layered navigation
     *
     * @param bool $isFilterableInSearch
     * @return $this
     */
    public function setIsFilterableInSearch($isFilterableInSearch);

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
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
     */
    public function getApplyTo();

    /**
     * Set apply to value for the element
     *
     * @param string[]|string $applyTo
     * @return $this
     */
    public function setApplyTo($applyTo);

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @return string|null
     */
    public function getIsSearchable();

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @param string $isSearchable
     * @return $this
     */
    public function setIsSearchable($isSearchable);

    /**
     * Whether the attribute can be used in Advanced Search
     *
     * @return string|null
     */
    public function getIsVisibleInAdvancedSearch();

    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param string $isVisibleInAdvancedSearch
     * @return $this
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch);

    /**
     * Whether the attribute can be compared on the frontend
     *
     * @return string|null
     */
    public function getIsComparable();

    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param string $isComparable
     * @return $this
     */
    public function setIsComparable($isComparable);

    /**
     * Whether the attribute can be used for promo rules
     *
     * @return string|null
     */
    public function getIsUsedForPromoRules();

    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param string $isUsedForPromoRules
     * @return $this
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules);

    /**
     * Whether the attribute is visible on the frontend
     *
     * @return string|null
     */
    public function getIsVisibleOnFront();

    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param string $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * Whether the attribute can be used in product listing
     *
     * @return string|null
     */
    public function getUsedInProductListing();

    /**
     * Set whether the attribute can be used in product listing
     *
     * @param string $usedInProductListing
     * @return $this
     */
    public function setUsedInProductListing($usedInProductListing);

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool|null
     */
    public function getIsVisible();

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible);

    /**
     * Retrieve attribute scope
     *
     * @return string|null
     */
    public function getScope();

    /**
     * Set attribute scope
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope);

    /**
     * @return \Magento\Catalog\Api\Data\EavAttributeExtensionInterface|null
     */
    public function getExtensionAttributes();
}
