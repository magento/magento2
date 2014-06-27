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

use Magento\Framework\Service\Data\Eav\AbstractObjectBuilder;

/**
 * Class AttributeMetadataBuilder
 */
class AttributeMetadataBuilder extends AbstractObjectBuilder
{
    /**
     * Option builder
     *
     * @var OptionBuilder
     */
    protected $optionBuilder;

    /**
     * Validation rule builder
     *
     * @var ValidationRuleBuilder
     */
    protected $validationRuleBuilder;

    /**
     * @var Product\Attribute\FrontendLabelBuilder
     */
    protected $frontendLabelBuilder;

    /**
     * Initializes builder.
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param \Magento\Framework\Service\Data\Eav\AttributeValueBuilder $valueBuilder
     * @param OptionBuilder $optionBuilder
     * @param ValidationRuleBuilder $validationRuleBuilder
     * @param Product\Attribute\FrontendLabelBuilder $frontendLabelBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        \Magento\Framework\Service\Data\Eav\AttributeValueBuilder $valueBuilder,
        OptionBuilder $optionBuilder,
        ValidationRuleBuilder $validationRuleBuilder,
        Product\Attribute\FrontendLabelBuilder $frontendLabelBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder);
        $this->optionBuilder = $optionBuilder;
        $this->validationRuleBuilder = $validationRuleBuilder;
        $this->frontendLabelBuilder = $frontendLabelBuilder;
        $this->_data[AttributeMetadata::OPTIONS] = array();
        $this->_data[AttributeMetadata::VALIDATION_RULES] = array();
        $this->_data[AttributeMetadata::FRONTEND_LABEL] = array();
    }

    /**
     * Set attribute id
     *
     * @param  int $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_ID, $attributeId);
    }

    /**
     * Set attribute code
     *
     * @param  string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->_set(AttributeMetadata::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set whether the attribute system or not
     *
     * @param  bool $isSystem
     * @return $this
     */
    public function setSystem($isSystem)
    {
        return $this->_set(AttributeMetadata::SYSTEM, $isSystem);
    }

    /**
     * Set front end input
     *
     * @param  string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput)
    {
        return $this->_set(AttributeMetadata::FRONTEND_INPUT, $frontendInput);
    }

    /**
     * Set validation rules
     *
     * @param  \Magento\Catalog\Service\V1\Data\Eav\ValidationRule[] $validationRules
     * @return $this
     */
    public function setValidationRules($validationRules)
    {
        return $this->_set(AttributeMetadata::VALIDATION_RULES, $validationRules);
    }

    /**
     * Set options
     * 
     * @param  \Magento\Catalog\Service\V1\Data\Eav\Option[] $options
     * @return $this
     */
    public function setOptions($options)
    {
        return $this->_set(AttributeMetadata::OPTIONS, $options);
    }

    /**
     * Set visible
     *
     * @param  bool $visible
     * @return $this
     */
    public function setVisible($visible)
    {
        return $this->_set(AttributeMetadata::VISIBLE, $visible);
    }

    /**
     * Set required
     *
     * @param  bool $required
     * @return $this
     */
    public function setRequired($required)
    {
        return $this->_set(AttributeMetadata::REQUIRED, $required);
    }

    /**
     * Set is user defined
     *
     * @param  bool $isUserDefined
     * @return $this
     */
    public function setUserDefined($isUserDefined)
    {
        return $this->_set(AttributeMetadata::USER_DEFINED, $isUserDefined);
    }

    /**
     * Set front end label
     *
     * @param  \Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabel[] $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->_set(AttributeMetadata::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * Set note
     *
     * @param  string $note
     * @return $this
     */
    public function setNote($note)
    {
        return $this->_set(AttributeMetadata::NOTE, $note);
    }

    /**
     * @param  string $backendType
     * @return AttributeMetadataBuilder
     */
    public function setBackendType($backendType)
    {
        return $this->_set(AttributeMetadata::BACKEND_TYPE, $backendType);
    }

    /**
     * Set backend model
     *
     * @param  string $value
     * @return $this
     */
    public function setBackendModel($value)
    {
        return $this->_set(AttributeMetadata::BACKEND_MODEL, $value);
    }

    /**
     * Set source model
     *
     * @param  string $value
     * @return $this
     */
    public function setSourceModel($value)
    {
        return $this->_set(AttributeMetadata::SOURCE_MODEL, $value);
    }

    /**
     * Set default value for the element
     *
     * @param  mixed $value
     * @return $this
     */
    public function setDefaultValue($value)
    {
        return $this->_set(AttributeMetadata::DEFAULT_VALUE, $value);
    }

    /**
     * Set whether this is a unique attribute
     *
     * @param  bool $isUnique
     * @return $this
     */
    public function setUnique($isUnique)
    {
        return $this->_set(AttributeMetadata::UNIQUE, $isUnique);
    }

    /**
     * Set apply to value for the element
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
     * @param  array|string|null $applyTo
     * @return $this
     */
    public function setApplyTo($applyTo)
    {
        return $this->_set(AttributeMetadata::APPLY_TO, $this->processApplyToValue($applyTo));
    }

    /**
     * Process applyTo value
     *
     * Transform string to array
     *
     * @param  string|array $applyTo
     * @return array
     */
    protected function processApplyToValue($applyTo)
    {
        $value = array();
        if (is_array($applyTo)) {
            $value = $applyTo;
        } elseif (is_string($applyTo)) {
            $value = explode(',', $applyTo);
        }
        return $value;
    }

    /**
     * Set whether the attribute can be used for configurable products
     *
     * @param  bool $isConfigurable
     * @return $this
     */
    public function setConfigurable($isConfigurable)
    {
        return $this->_set(AttributeMetadata::CONFIGURABLE, $isConfigurable);
    }

    /**
     * Set whether the attribute can be used in Quick Search
     *
     * @param  bool $isSearchable
     * @return $this
     */
    public function setSearchable($isSearchable)
    {
        return $this->_set(AttributeMetadata::SEARCHABLE, $isSearchable);
    }

    /**
     * Set whether the attribute can be used in Advanced Search
     *
     * @param  bool $isVisibleInAdvancedSearch
     * @return $this
     */
    public function setVisibleInAdvancedSearch($isVisibleInAdvancedSearch)
    {
        return $this->_set(AttributeMetadata::VISIBLE_IN_ADVANCED_SEARCH, $isVisibleInAdvancedSearch);
    }

    /**
     * Set whether the attribute can be compared on the frontend
     *
     * @param  bool $isComparable
     * @return $this
     */
    public function setComparable($isComparable)
    {
        return $this->_set(AttributeMetadata::COMPARABLE, $isComparable);
    }

    /**
     * Set whether the attribute can be used for promo rules
     *
     * @param  bool $isUsedForPromoRules
     * @return $this
     */
    public function setUsedForPromoRules($isUsedForPromoRules)
    {
        return $this->_set(AttributeMetadata::USED_FOR_PROMO_RULES, $isUsedForPromoRules);
    }

    /**
     * Set whether the attribute is visible on the frontend
     *
     * @param  bool $isVisibleOnFront
     * @return $this
     */
    public function setVisibleOnFront($isVisibleOnFront)
    {
        return $this->_set(AttributeMetadata::VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * Set whether the attribute can be used in product listing
     *
     * @param  bool $usedInProductListing
     * @return $this
     */
    public function setUsedInProductListing($usedInProductListing)
    {
        return $this->_set(AttributeMetadata::USED_IN_PRODUCT_LISTING, $usedInProductListing);
    }

    /**
     * Set attribute scope value
     *
     * @param  string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        return $this->_set(AttributeMetadata::SCOPE, $scope);
    }

    /**
     * Set whether it is used for sorting in product listing
     *
     * @param  bool $usedForSortBy
     * @return $this
     */
    public function setUsedForSortBy($usedForSortBy)
    {
        return $this->_set(AttributeMetadata::USED_FOR_SORT_BY, (bool)$usedForSortBy);
    }

    /**
     * Set whether it used in layered navigation
     *
     * @param  bool $isFilterable
     * @return $this
     */
    public function setFilterable($isFilterable)
    {
        return $this->_set(AttributeMetadata::FILTERABLE, (bool)$isFilterable);
    }

    /**
     * Set whether it is used in search results layered navigation
     *
     * @param  bool $isFilterableInSearch
     * @return $this
     */
    public function setFilterableInSearch($isFilterableInSearch)
    {
        return $this->_set(AttributeMetadata::FILTERABLE_IN_SEARCH, (bool)$isFilterableInSearch);
    }

    /**
     * Set position
     *
     * @param  int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->_set(AttributeMetadata::POSITION, (int)$position);
    }

    /**
     * Set whether WYSIWYG enabled or not
     *
     * @param  bool $isWysiwygEnabled
     * @return $this
     */
    public function setWysiwygEnabled($isWysiwygEnabled)
    {
        return $this->_set(AttributeMetadata::WYSIWYG_ENABLED, (bool)$isWysiwygEnabled);
    }

    /**
     * Set whether the HTML tags are allowed on the frontend
     *
     * @param  bool $isHtmlAllowedOnFront
     * @return $this
     */
    public function setHtmlAllowedOnFront($isHtmlAllowedOnFront)
    {
        return $this->_set(AttributeMetadata::HTML_ALLOWED_ON_FRONT, (bool)$isHtmlAllowedOnFront);
    }

    /**
     * Set frontend class for attribute
     *
     * @param  string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass)
    {
        return $this->_set(AttributeMetadata::FRONTEND_CLASS, $frontendClass);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(AttributeMetadata::OPTIONS, $data)) {
            $options = array();
            if (is_array($data[AttributeMetadata::OPTIONS])) {
                foreach ($data[AttributeMetadata::OPTIONS] as $key => $option) {
                    $options[$key] = $this->optionBuilder->populateWithArray($option)->create();
                }
            }
            $validationRules = array();
            if (is_array($data[AttributeMetadata::VALIDATION_RULES])) {
                foreach ($data[AttributeMetadata::VALIDATION_RULES] as $key => $value) {
                    $validationRules[$key] = $this->validationRuleBuilder->populateWithArray($value)->create();
                }
            }

            $data[AttributeMetadata::OPTIONS] = $options;
            $data[AttributeMetadata::VALIDATION_RULES] = $validationRules;
        }

        // fill frontend labels
        if (isset($data[AttributeMetadata::FRONTEND_LABEL]) && is_array($data[AttributeMetadata::FRONTEND_LABEL])) {
            $frontendLabel = [];
            foreach ($data[AttributeMetadata::FRONTEND_LABEL] as $key => $value) {
                $frontendLabel[$key] = $this->frontendLabelBuilder->populateWithArray($value)->create();
            }
            $data[AttributeMetadata::FRONTEND_LABEL] = $frontendLabel;
        }

        if (array_key_exists(AttributeMetadata::APPLY_TO, $data)) {
            $data[AttributeMetadata::APPLY_TO] = $this->processApplyToValue($data[AttributeMetadata::APPLY_TO]);
        }

        return parent::_setDataValues($data);
    }
}
