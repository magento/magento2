<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Data\Eav\Category;

use Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder;
use Magento\Framework\Service\Data\AttributeMetadataBuilderInterface;

/**
 * @codeCoverageIgnore
 */
class AttributeMetadataBuilder extends AbstractExtensibleObjectBuilder implements AttributeMetadataBuilderInterface
{
    /**
     * Set attribute id
     *
     * @param  int $value
     * @return $this
     */
    public function setAttributeId($value)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_ID, $value);
    }

    /**
     * Set attribute code
     *
     * @param  string $value
     * @return $this
     */
    public function setAttributeCode($value)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_CODE, $value);
    }

    /**
     * Name of the created category
     *
     * @param  string $value
     * @return $this
     */
    public function setName($value)
    {
        return $this->_set(AttributeMetadata::NAME, $value);
    }

    /**
     * Set whether the category will be visible in the frontend
     *
     * @param  bool $value
     * @return $this
     */
    public function setActive($value)
    {
        return $this->_set(AttributeMetadata::ACTIVE, $value);
    }

    /**
     * Set available options by which products in the category can be sorted
     *
     * @param  string[] $value
     * @return $this
     */
    public function setAvailableSortBy($value)
    {
        return $this->_set(AttributeMetadata::AVAILABLE_SORT_BY, $value);
    }

    /**
     * Set custom design for the category
     *
     * @param  string $value
     * @return $this
     */
    public function setCustomDesign($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_DESIGN, $value);
    }

    /**
     * Apply the custom design to all products assigned to the category
     *
     * @param  int $value
     * @return $this
     */
    public function setCustomApplyToProducts($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_APPLY_TO_PRODUCTS, $value);
    }

    /**
     * Set date starting from which the custom design will be applied to the category
     *
     * @param  string $value
     * @return $this
     */
    public function setCustomDesignFrom($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_DESIGN_FROM, $value);
    }

    /**
     * Set date till which the custom design will be applied to the category
     *
     * @param  string $value
     * @return $this
     */
    public function setCustomDesignTo($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_DESIGN_TO, $value);
    }

    /**
     * Custom layout update
     *
     * @param  string $value
     * @return $this
     */
    public function setCustomLayoutUpdate($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_LAYOUT_UPDATE, $value);
    }

    /**
     * Set the default option by which products in the category are sorted
     *
     * @param  string $value
     * @return $this
     */
    public function setDefaultSortBy($value)
    {
        return $this->_set(AttributeMetadata::DEFAULT_SORT_BY, $value);
    }

    /**
     * Category description
     *
     * @param  string $value
     * @return $this
     */
    public function setDescription($value)
    {
        return $this->_set(AttributeMetadata::DESCRIPTION, $value);
    }

    /**
     * Set content that will be displayed on the category view page
     *
     * @param  string $value
     * @return $this
     */
    public function setDisplayMode($value)
    {
        return $this->_set(AttributeMetadata::DISPLAY_MODE, $value);
    }

    /**
     * Set whether the category will be anchored
     *
     * @param  bool $value
     * @return $this
     */
    public function setAnchor($value)
    {
        return $this->_set(AttributeMetadata::ANCHOR, $value);
    }

    /**
     * Landing page
     *
     * @param  int $value
     * @return $this
     */
    public function setLandingPage($value)
    {
        return $this->_set(AttributeMetadata::LANDING_PAGE, $value);
    }

    /**
     * Set category meta description
     *
     * @param  string $value
     * @return $this
     */
    public function setMetaDescription($value)
    {
        return $this->_set(AttributeMetadata::META_DESCRIPTION, $value);
    }

    /**
     * Set category meta keywords
     *
     * @param  string $value
     * @return $this
     */
    public function setMetaKeywords($value)
    {
        return $this->_set(AttributeMetadata::META_KEYWORDS, $value);
    }

    /**
     * Set category meta title
     *
     * @param  string $value
     * @return $this
     */
    public function setMetaTitle($value)
    {
        return $this->_set(AttributeMetadata::META_TITLE, $value);
    }

    /**
     * Set type of page layout that the category should use
     *
     * @param  string $value
     * @return $this
     */
    public function setPageLayout($value)
    {
        return $this->_set(AttributeMetadata::PAGE_LAYOUT, $value);
    }

    /**
     * Set a relative URL path which can be entered in place of the standard target path
     *
     * @param  string $value
     * @return $this
     */
    public function setUrlKey($value)
    {
        return $this->_set(AttributeMetadata::URL_KEY, $value);
    }

    /**
     * Set whether the category is visible on the top menu bar
     *
     * @param  bool $value
     * @return $this
     */
    public function setIncludeInMenu($value)
    {
        return $this->_set(AttributeMetadata::INCLUDE_IN_MENU, $value);
    }

    /**
     * Set price range of each price level displayed in the layered navigation block
     *
     * @param  string $value
     * @return $this
     */
    public function setFilterPriceRange($value)
    {
        return $this->_set(AttributeMetadata::FILTER_PRICE_RANGE, $value);
    }

    /**
     * Set price range of each price level displayed in the layered navigation block
     *
     * @param  int $value
     * @return $this
     */
    public function setCustomUseParentSettings($value)
    {
        return $this->_set(AttributeMetadata::CUSTOM_USE_PARENT_SETTINGS, $value);
    }
}
