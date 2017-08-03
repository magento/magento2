<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

/**
 * Class Attribute
 *
 * @method \Magento\Eav\Api\Data\AttributeExtensionInterface getExtensionAttributes()
 * @since 2.0.0
 */
class Attribute extends \Magento\Catalog\Model\Entity\Attribute implements
    \Magento\Catalog\Api\Data\CategoryAttributeInterface
{
    const SCOPE_STORE = 0;

    const SCOPE_GLOBAL = 1;

    const SCOPE_WEBSITE = 2;

    const KEY_IS_GLOBAL = 'is_global';

    /**
     * Retrieve apply to products array
     * Return empty array if applied to all products
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getApplyTo()
    {
        if ($this->getData(self::APPLY_TO)) {
            if (is_array($this->getData(self::APPLY_TO))) {
                return $this->getData(self::APPLY_TO);
            }
            return explode(',', $this->getData(self::APPLY_TO));
        } else {
            return [];
        }
    }

    /**
     * Set apply to value for the element
     *
     * @param string []|string
     * @return $this
     * @since 2.0.0
     */
    public function setApplyTo($applyTo)
    {
        if (is_array($applyTo)) {
            $applyTo = implode(',', $applyTo);
        }
        return $this->setData(self::APPLY_TO, $applyTo);
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsWysiwygEnabled()
    {
        return $this->getData(self::IS_WYSIWYG_ENABLED);
    }

    /**
     * Set whether WYSIWYG is enabled flag
     *
     * @param bool $isWysiwygEnabled
     * @return $this
     * @since 2.0.0
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        return $this->getData(self::IS_WYSIWYG_ENABLED, $isWysiwygEnabled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsHtmlAllowedOnFront()
    {
        return $this->getData(self::IS_HTML_ALLOWED_ON_FRONT);
    }

    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param bool $isHtmlAllowedOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront)
    {
        return $this->setData(self::IS_HTML_ALLOWED_ON_FRONT, $isHtmlAllowedOnFront);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUsedForSortBy()
    {
        return $this->getData(self::USED_FOR_SORT_BY);
    }

    /**
     * Set whether it is used for sorting in product listing
     *
     * @param bool $usedForSortBy
     * @return $this
     * @since 2.0.0
     */
    public function setUsedForSortBy($usedForSortBy)
    {
        return $this->setData(self::USED_FOR_SORT_BY, $usedForSortBy);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsFilterable()
    {
        return $this->getData(self::IS_FILTERABLE);
    }

    /**
     * Set whether it used in layered navigation
     *
     * @param bool $isFilterable
     * @return $this
     * @since 2.0.0
     */
    public function setIsFilterable($isFilterable)
    {
        return $this->setData(self::IS_FILTERABLE, $isFilterable);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsFilterableInSearch()
    {
        return $this->getData(self::IS_FILTERABLE_IN_SEARCH);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsUsedInGrid()
    {
        return (bool)$this->getData(self::IS_USED_IN_GRID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsVisibleInGrid()
    {
        return (bool)$this->getData(self::IS_VISIBLE_IN_GRID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsFilterableInGrid()
    {
        return (bool)$this->getData(self::IS_FILTERABLE_IN_GRID);
    }

    /**
     * Set whether it is used in search results layered navigation
     *
     * @param bool $isFilterableInSearch
     * @return $this
     * @since 2.0.0
     */
    public function setIsFilterableInSearch($isFilterableInSearch)
    {
        return $this->getData(self::IS_FILTERABLE_IN_SEARCH, $isFilterableInSearch);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsSearchable()
    {
        return $this->getData(self::IS_SEARCHABLE);
    }

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @param string $isSearchable
     * @return $this
     * @since 2.0.0
     */
    public function setIsSearchable($isSearchable)
    {
        return $this->setData(self::IS_SEARCHABLE, $isSearchable);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsVisibleInAdvancedSearch()
    {
        return $this->getData(self::IS_VISIBLE_IN_ADVANCED_SEARCH);
    }

    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param string $isVisibleInAdvancedSearch
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch)
    {
        return $this->setData(self::IS_VISIBLE_IN_ADVANCED_SEARCH, $isVisibleInAdvancedSearch);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsComparable()
    {
        return $this->getData(self::IS_COMPARABLE);
    }

    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param string $isComparable
     * @return $this
     * @since 2.0.0
     */
    public function setIsComparable($isComparable)
    {
        return $this->setData(self::IS_COMPARABLE, $isComparable);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsUsedForPromoRules()
    {
        return $this->getData(self::IS_USED_FOR_PROMO_RULES);
    }

    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param string $isUsedForPromoRules
     * @return $this
     * @since 2.0.0
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules)
    {
        return $this->setData(self::IS_USED_FOR_PROMO_RULES, $isUsedForPromoRules);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(self::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param string $isVisibleOnFront
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(self::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getUsedInProductListing()
    {
        return $this->getData(self::USED_IN_PRODUCT_LISTING);
    }

    /**
     * Set whether the attribute can be used in product listing
     *
     * @param string $usedInProductListing
     * @return $this
     * @since 2.0.0
     */
    public function setUsedInProductListing($usedInProductListing)
    {
        return $this->setData(self::USED_IN_PRODUCT_LISTING, $usedInProductListing);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getIsVisible()
    {
        return $this->getData(self::IS_VISIBLE);
    }

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     * @since 2.0.0
     */
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::IS_VISIBLE, $isVisible);
    }

    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getScope()
    {
        $scope = $this->getData(self::KEY_IS_GLOBAL);
        if ($scope == self::SCOPE_GLOBAL) {
            return self::SCOPE_GLOBAL_TEXT;
        } elseif ($scope == self::SCOPE_WEBSITE) {
            return self::SCOPE_WEBSITE_TEXT;
        } else {
            return self::SCOPE_STORE_TEXT;
        }
    }

    /**
     * Set attribute scope
     *
     * @param string $scope
     * @return $this
     * @since 2.0.0
     */
    public function setScope($scope)
    {
        if ($scope == 'global') {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_GLOBAL);
        } elseif ($scope == 'website') {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_WEBSITE);
        } elseif ($scope == 'store') {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_STORE);
        } else {
            //Ignore unrecognized scope
            return $this;
        }
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        $this->setData(self::IS_USED_IN_GRID, $isUsedInGrid);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsVisibleInGrid($isVisibleInGrid)
    {
        $this->setData(self::IS_VISIBLE_IN_GRID, $isVisibleInGrid);
        return $this;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsFilterableInGrid($isFilterableInGrid)
    {
        $this->setData(self::IS_FILTERABLE_IN_GRID, $isFilterableInGrid);
        return $this;
    }
}
