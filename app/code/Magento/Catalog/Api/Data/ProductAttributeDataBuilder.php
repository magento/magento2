<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\ObjectFactory;

/**
 * DataBuilder class for \Magento\Catalog\Api\Data\ProductAttributeInterface
 * @codeCoverageIgnore
 */
class ProductAttributeDataBuilder extends \Magento\Framework\Api\Builder
{
    /**
     * @param ObjectFactory $objectFactory
     * @param MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string $modelClassInterface
     */
    public function __construct(
        ObjectFactory $objectFactory,
        MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = 'Magento\Catalog\Api\Data\ProductAttributeInterface'
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            $modelClassInterface
        );
    }

    /**
     * @param bool|null $isWysiwygEnabled
     * @return $this
     */
    public function setIsWysiwygEnabled($isWysiwygEnabled)
    {
        $this->_set('is_wysiwyg_enabled', $isWysiwygEnabled);
        return $this;
    }

    /**
     * @param bool|null $isHtmlAllowedOnFront
     * @return $this
     */
    public function setIsHtmlAllowedOnFront($isHtmlAllowedOnFront)
    {
        $this->_set('is_html_allowed_on_front', $isHtmlAllowedOnFront);
        return $this;
    }

    /**
     * @param bool|null $usedForSortBy
     * @return $this
     */
    public function setUsedForSortBy($usedForSortBy)
    {
        $this->_set('used_for_sort_by', $usedForSortBy);
        return $this;
    }

    /**
     * @param bool|null $isFilterable
     * @return $this
     */
    public function setIsFilterable($isFilterable)
    {
        $this->_set('is_filterable', $isFilterable);
        return $this;
    }

    /**
     * @param bool|null $isFilterableInSearch
     * @return $this
     */
    public function setIsFilterableInSearch($isFilterableInSearch)
    {
        $this->_set('is_filterable_in_search', $isFilterableInSearch);
        return $this;
    }

    /**
     * @param int|null $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->_set('position', $position);
        return $this;
    }

    /**
     * @param string $applyTo
     * @return $this
     */
    public function setApplyTo($applyTo)
    {
        $this->_set('apply_to', $applyTo);
        return $this;
    }

    /**
     * @param string|null $isConfigurable
     * @return $this
     */
    public function setIsConfigurable($isConfigurable)
    {
        $this->_set('is_configurable', $isConfigurable);
        return $this;
    }

    /**
     * @param string|null $isSearchable
     * @return $this
     */
    public function setIsSearchable($isSearchable)
    {
        $this->_set('is_searchable', $isSearchable);
        return $this;
    }

    /**
     * @param string|null $isVisibleInAdvancedSearch
     * @return $this
     */
    public function setIsVisibleInAdvancedSearch($isVisibleInAdvancedSearch)
    {
        $this->_set('is_visible_in_advanced_search', $isVisibleInAdvancedSearch);
        return $this;
    }

    /**
     * @param string|null $isComparable
     * @return $this
     */
    public function setIsComparable($isComparable)
    {
        $this->_set('is_comparable', $isComparable);
        return $this;
    }

    /**
     * @param string|null $isUsedForPromoRules
     * @return $this
     */
    public function setIsUsedForPromoRules($isUsedForPromoRules)
    {
        $this->_set('is_used_for_promo_rules', $isUsedForPromoRules);
        return $this;
    }

    /**
     * @param string|null $isVisibleOnFront
     * @return $this
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->_set('is_visible_on_front', $isVisibleOnFront);
        return $this;
    }

    /**
     * @param string|null $usedInProductListing
     * @return $this
     */
    public function setUsedInProductListing($usedInProductListing)
    {
        $this->_set('used_in_product_listing', $usedInProductListing);
        return $this;
    }

    /**
     * @param bool|null $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        $this->_set('is_visible', $isVisible);
        return $this;
    }

    /**
     * @param string|null $scope
     * @return $this
     */
    public function setScope($scope)
    {
        $this->_set('scope', $scope);
        return $this;
    }

    /**
     * @param int|null $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId)
    {
        $this->_set('attribute_id', $attributeId);
        return $this;
    }

    /**
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        $this->_set('attribute_code', $attributeCode);
        return $this;
    }

    /**
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput)
    {
        $this->_set('frontend_input', $frontendInput);
        return $this;
    }

    /**
     * @param string|null $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId)
    {
        $this->_set('entity_type_id', $entityTypeId);
        return $this;
    }

    /**
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired)
    {
        $this->_set('is_required', $isRequired);
        return $this;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->_set('options', $options);
        return $this;
    }

    /**
     * @param bool|null $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined)
    {
        $this->_set('is_user_defined', $isUserDefined);
        return $this;
    }

    /**
     * @param string $frontendLabel
     * @return $this
     */
    public function setDefaultFrontendLabel($frontendLabel)
    {
        $this->_set('frontend_label', $frontendLabel);
        return $this;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeFrontendLabelInterface[]
     * $storeFrontendLabels
     * @return $this
     */
    public function setFrontendLabels($storeFrontendLabels)
    {
        $this->_set('frontend_labels', $storeFrontendLabels);
        return $this;
    }

    /**
     * @param string|null $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->_set('note', $note);
        return $this;
    }

    /**
     * @param string|null $backendType
     * @return $this
     */
    public function setBackendType($backendType)
    {
        $this->_set('backend_type', $backendType);
        return $this;
    }

    /**
     * @param string|null $backendModel
     * @return $this
     */
    public function setBackendModel($backendModel)
    {
        $this->_set('backend_model', $backendModel);
        return $this;
    }

    /**
     * @param string|null $sourceModel
     * @return $this
     */
    public function setSourceModel($sourceModel)
    {
        $this->_set('source_model', $sourceModel);
        return $this;
    }

    /**
     * @param string|null $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->_set('default_value', $defaultValue);
        return $this;
    }

    /**
     * @param string|null $isUnique
     * @return $this
     */
    public function setIsUnique($isUnique)
    {
        $this->_set('is_unique', $isUnique);
        return $this;
    }

    /**
     * @param string|null $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass)
    {
        $this->_set('frontend_class', $frontendClass);
        return $this;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeValidationRuleInterface $validationRules
     * @return $this
     */
    public function setValidationRules($validationRules)
    {
        $this->_set('validation_rules', $validationRules);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        /** TODO: temporary fix while problem with hasDataChanges flag not solved. MAGETWO-30324 */
        $object = parent::create();
        $object->setDataChanges(true);
        return $object;
    }
}
