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

use \Magento\Framework\Service\Data\AbstractExtensibleObject;
use Magento\Framework\Service\Data\MetadataObjectInterface;

/**
 * @codeCoverageIgnore
 */
class AttributeMetadata extends AbstractExtensibleObject implements MetadataObjectInterface
{
    const ATTRIBUTE_ID = 'attribute_id';

    const ATTRIBUTE_CODE = 'attribute_code';

    const NAME = 'name';

    const ACTIVE = 'active';

    const AVAILABLE_SORT_BY = 'available_sort_by';

    const CUSTOM_DESIGN = 'custom_design';

    const CUSTOM_APPLY_TO_PRODUCTS = 'custom_apply_to_products';

    const CUSTOM_DESIGN_FROM = 'custom_design_from';

    const CUSTOM_DESIGN_TO = 'custom_design_to';

    const CUSTOM_LAYOUT_UPDATE = 'custom_layout_update';

    const DEFAULT_SORT_BY = 'default_sort_by';

    const DESCRIPTION = 'description';

    const DISPLAY_MODE = 'display_mode';

    const ANCHOR = 'anchor';

    const LANDING_PAGE = 'landing_page';

    const META_DESCRIPTION = 'meta_description';

    const META_KEYWORDS = 'meta_keywords';

    const META_TITLE = 'meta_title';

    const PAGE_LAYOUT = 'page_layout';

    const URL_KEY = 'url_key';

    const INCLUDE_IN_MENU = 'include_in_menu';

    const FILTER_PRICE_RANGE = 'filter_price_range';

    const CUSTOM_USE_PARENT_SETTINGS = 'custom_use_parent_settings';

    /**
     * Retrieve id of the attribute.
     *
     * @return int|null
     */
    public function getAttributeId()
    {
        return $this->_get(self::ATTRIBUTE_ID);
    }

    /**
     * Retrieve code of the attribute.
     *
     * @return string|null
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * Name of the created category
     *
     * @return string
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Defines whether the category will be visible in the frontend
     *
     * @return bool|null
     */
    public function isActive()
    {
        return $this->_get(self::ACTIVE);
    }

    /**
     * All available options by which products in the category can be sorted
     *
     * @return string[]|null
     */
    public function getAvailableSortBy()
    {
        return $this->_get(self::AVAILABLE_SORT_BY);
    }

    /**
     * The custom design for the category (optional)
     *
     * @return string|null
     */
    public function getCustomDesign()
    {
        return $this->_get(self::CUSTOM_DESIGN);
    }

    /**
     * Apply the custom design to all products assigned to the category
     *
     * @return int|null
     */
    public function getCustomApplyToProducts()
    {
        return $this->_get(self::CUSTOM_APPLY_TO_PRODUCTS);
    }

    /**
     * Date starting from which the custom design will be applied to the category
     *
     * @return string|null
     */
    public function getCustomDesignFrom()
    {
        return $this->_get(self::CUSTOM_DESIGN_FROM);
    }

    /**
     * Date till which the custom design will be applied to the category
     *
     * @return string|null
     */
    public function getCustomDesignTo()
    {
        return $this->_get(self::CUSTOM_DESIGN_TO);
    }

    /**
     * Custom layout update
     *
     * @return string|null
     */
    public function getCustomLayoutUpdate()
    {
        return $this->_get(self::CUSTOM_LAYOUT_UPDATE);
    }

    /**
     * The default option by which products in the category are sorted
     *
     * @return string|null
     */
    public function getDefaultSortBy()
    {
        return $this->_get(self::DEFAULT_SORT_BY);
    }

    /**
     * Category description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Content that will be displayed on the category view page
     *
     * @return string|null
     */
    public function getDisplayMode()
    {
        return $this->_get(self::DISPLAY_MODE);
    }

    /**
     * Defines whether the category will be anchored
     *
     * @return bool|null
     */
    public function isAnchor()
    {
        return $this->_get(self::ANCHOR);
    }

    /**
     * Landing page
     *
     * @return int|null
     */
    public function getLandingPage()
    {
        return $this->_get(self::LANDING_PAGE);
    }

    /**
     * Category meta description
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->_get(self::META_DESCRIPTION);
    }

    /**
     * Category meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return $this->_get(self::META_KEYWORDS);
    }

    /**
     * Category meta title
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->_get(self::META_TITLE);
    }

    /**
     * Type of page layout that the category should use
     *
     * @return string|null
     */
    public function getPageLayout()
    {
        return $this->_get(self::PAGE_LAYOUT);
    }

    /**
     * A relative URL path which can be entered in place of the standard target path
     *
     * @return string|null
     */
    public function getUrlKey()
    {
        return $this->_get(self::URL_KEY);
    }

    /**
     * Defines whether the category is visible on the top menu bar
     *
     * @return bool|null
     */
    public function getIncludeInMenu()
    {
        return $this->_get(self::INCLUDE_IN_MENU);
    }

    /**
     * Price range of each price level displayed in the layered navigation block
     *
     * @return string|null
     */
    public function getFilterPriceRange()
    {
        return $this->_get(self::FILTER_PRICE_RANGE);
    }

    /**
     * Price range of each price level displayed in the layered navigation block
     *
     * @return bool|null
     */
    public function getCustomUseParentSettings()
    {
        return $this->_get(self::CUSTOM_USE_PARENT_SETTINGS);
    }
}
