<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

class Attribute extends \Magento\Catalog\Model\Entity\Attribute implements
    \Magento\Catalog\Api\Data\CategoryAttributeInterface
{
    /**
     * Retrieve apply to products array
     * Return empty array if applied to all products
     *
     * @return string[]
     */
    public function getApplyTo()
    {
        if ($this->getData('apply_to')) {
            if (is_array($this->getData('apply_to'))) {
                return $this->getData('apply_to');
            }
            return explode(',', $this->getData('apply_to'));
        } else {
            return [];
        }
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getIsHtmlAllowedOnFront()
    {
        return $this->getData(self::IS_HTML_ALLOWED_ON_FRONT);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedForSortBy()
    {
        return $this->getData(self::USED_FOR_SORT_BY);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsWysiwygEnabled()
    {
        return $this->getData(self::IS_WYSIWYG_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFilterable()
    {
        return $this->getData(self::IS_FILTERABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFilterableInSearch()
    {
        return $this->getData(self::IS_FILTERABLE_IN_SEARCH);
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSearchable()
    {
        return $this->getData(self::IS_SEARCHABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsConfigurable()
    {
        return $this->getData(self::IS_CONFIGURABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVisibleInAdvancedSearch()
    {
        return $this->getData(self::IS_VISIBLE_IN_ADVANCED_SEARCH);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsComparable()
    {
        return $this->getData(self::IS_COMPARABLE);
    }
    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->isScopeGlobal() ? 'global' : ($this->isScopeWebsite() ? 'website' : 'store');
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(self::IS_VISIBLE_ON_FRONT);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsUsedForPromoRules()
    {
        return $this->getData(self::IS_USED_FOR_PROMO_RULES);
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedInProductListing()
    {
        return $this->getData(self::USED_IN_PRODUCT_LISTING);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVisible()
    {
        return $this->getData(self::IS_VISIBLE);
    }
    //@codeCoverageIgnoreEnd
}
