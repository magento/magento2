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
namespace Magento\Catalog\Service\V1\Data\Eav;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;
use Magento\Framework\Service\Data\MetadataObjectInterface;

/**
 * Class AttributeMetadata
 *
 * @codeCoverageIgnore
 */
class AttributeMetadata extends AbstractExtensibleObject implements MetadataObjectInterface
{
    /**#@+
     * Constants used as keys into $_data
     */

    const ID = 'id';
    const CODE = 'code';

    const ATTRIBUTE_ID = 'attribute_id';

    const ATTRIBUTE_CODE = 'attribute_code';

    const FRONTEND_INPUT = 'frontend_input';

    const VALIDATION_RULES = 'validation_rules';

    const OPTIONS = 'options';

    const SYSTEM = 'system';

    const VISIBLE = 'visible';

    const REQUIRED = 'required';

    const USER_DEFINED = 'user_defined';

    const FRONTEND_LABEL = 'frontend_label';

    const NOTE = 'note';

    const BACKEND_TYPE = 'backend_type';

    const SOURCE_MODEL = 'source_model';

    const BACKEND_MODEL = 'backend_model';

    const DEFAULT_VALUE = 'default_value';

    const UNIQUE = 'unique';

    const APPLY_TO = 'apply_to';

    const CONFIGURABLE = 'configurable';

    const SEARCHABLE = 'searchable';

    const VISIBLE_IN_ADVANCED_SEARCH = 'visible_in_advanced_search';

    const COMPARABLE = 'comparable';

    const USED_FOR_PROMO_RULES = 'used_for_promo_rules';

    const VISIBLE_ON_FRONT = 'visible_on_front';

    const USED_IN_PRODUCT_LISTING = 'used_in_product_listing';

    const SCOPE = 'scope';

    // additional fields
    const WYSIWYG_ENABLED = 'wysiwyg_enabled';

    const HTML_ALLOWED_ON_FRONT = 'html_allowed_on_front';

    const FRONTEND_CLASS = 'frontend_class';

    const USED_FOR_SORT_BY = 'used_for_sort_by';

    const FILTERABLE = 'filterable';

    const FILTERABLE_IN_SEARCH = 'filterable_in_search';

    const POSITION = 'position';
    /**#@-*/

    /**
     * Retrieve id of the attribute.
     *
     * @return string|null
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
     * Retrieve is system attribute flag
     *
     * @return bool|null
     */
    public function isSystem()
    {
        return $this->_get(self::SYSTEM);
    }

    /**
     * Frontend HTML for input element.
     *
     * @return string|null
     */
    public function getFrontendInput()
    {
        return $this->_get(self::FRONTEND_INPUT);
    }

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Catalog\Service\V1\Data\Eav\ValidationRule[]|null
     */
    public function getValidationRules()
    {
        return $this->_get(self::VALIDATION_RULES);
    }

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool|null
     */
    public function isVisible()
    {
        return $this->_get(self::VISIBLE);
    }

    /**
     * Whether attribute is required.
     *
     * @return bool|null
     */
    public function isRequired()
    {
        return $this->_get(self::REQUIRED);
    }

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Catalog\Service\V1\Data\Eav\Option[]|null
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool|null
     */
    public function isUserDefined()
    {
        return $this->_get(self::USER_DEFINED);
    }

    /**
     * Get label which supposed to be displayed on frontend.
     *
     * @return \Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel[]|null
     */
    public function getFrontendLabel()
    {
        return $this->_get(self::FRONTEND_LABEL);
    }

    /**
     * Get the note attribute for the element.
     *
     * @return string|null
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * Get backend type.
     *
     * @return string|null
     */
    public function getBackendType()
    {
        return $this->_get(self::BACKEND_TYPE);
    }

    /**
     * Get backend model
     *
     * @return string|null
     */
    public function getBackendModel()
    {
        return $this->_get(self::BACKEND_MODEL);
    }

    /**
     * Get source model
     *
     * @return string|null
     */
    public function getSourceModel()
    {
        return $this->_get(self::SOURCE_MODEL);
    }

    /**
     * Get default value for the element.
     *
     * @return string|null
     */
    public function getDefaultValue()
    {
        return $this->_get(self::DEFAULT_VALUE);
    }

    /**
     * Whether this is a unique attribute
     *
     * @return string|null
     */
    public function isUnique()
    {
        return $this->_get(self::UNIQUE);
    }

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
    public function getApplyTo()
    {
        return $this->_get(self::APPLY_TO);
    }

    /**
     * Whether the attribute can be used for configurable products
     *
     * @return string|null
     */
    public function isConfigurable()
    {
        return $this->_get(self::CONFIGURABLE);
    }

    /**
     * Whether the attribute can be used in Quick Search
     *
     * @return string|null
     */
    public function isSearchable()
    {
        return $this->_get(self::SEARCHABLE);
    }

    /**
     * Whether the attribute can be used in Advanced Search
     *
     * @return string|null
     */
    public function isVisibleInAdvancedSearch()
    {
        return $this->_get(self::VISIBLE_IN_ADVANCED_SEARCH);
    }

    /**
     * Whether the attribute can be compared on the frontend
     *
     * @return string|null
     */
    public function isComparable()
    {
        return $this->_get(self::COMPARABLE);
    }

    /**
     * Whether the attribute can be used for promo rules
     *
     * @return string|null
     */
    public function isUsedForPromoRules()
    {
        return $this->_get(self::USED_FOR_PROMO_RULES);
    }

    /**
     * Whether the attribute is visible on the frontend
     *
     * @return string|null
     */
    public function isVisibleOnFront()
    {
        return $this->_get(self::VISIBLE_ON_FRONT);
    }

    /**
     * Whether the attribute can be used in product listing
     *
     * @return string|null
     */
    public function getUsedInProductListing()
    {
        return $this->_get(self::USED_IN_PRODUCT_LISTING);
    }

    /**
     * Retrieve attribute scope
     *
     * @return string|null
     */
    public function getScope()
    {
        return $this->_get(self::SCOPE);
    }

    /**
     * Retrieve frontend class of attribute
     *
     * @return string|null
     */
    public function getFrontendClass()
    {
        return $this->_get(self::FRONTEND_CLASS);
    }

    /**
     * Enable WYSIWYG flag
     *
     * @return bool|null
     */
    public function isWysiwygEnabled()
    {
        return (bool)$this->_get(self::WYSIWYG_ENABLED);
    }

    /**
     * Whether the HTML tags are allowed on the frontend
     *
     * @return bool|null
     */
    public function isHtmlAllowedOnFront()
    {
        return (bool)$this->_get(self::HTML_ALLOWED_ON_FRONT);
    }

    /**
     * Whether it is used for sorting in product listing
     *
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUsedForSortBy()
    {
        return (bool)$this->_get(self::USED_FOR_SORT_BY);
    }

    /**
     * Whether it used in layered navigation
     *
     * @return bool|null
     */
    public function isFilterable()
    {
        return (bool)$this->_get(self::FILTERABLE);
    }

    /**
     * Whether it is used in search results layered navigation
     *
     * @return bool|null
     */
    public function isFilterableInSearch()
    {
        return (bool)$this->_get(self::FILTERABLE_IN_SEARCH);
    }

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition()
    {
        return (int)$this->_get(self::POSITION);
    }
}
