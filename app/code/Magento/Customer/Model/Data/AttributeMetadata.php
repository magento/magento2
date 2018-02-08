<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Data;

use Magento\Customer\Api\Data\AttributeMetadataInterface;

/**
 * Customer attribute metadata class.
 */
class AttributeMetadata extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\Customer\Api\Data\AttributeMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->_get(self::ATTRIBUTE_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendInput()
    {
        return $this->_get(self::FRONTEND_INPUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilter()
    {
        return $this->_get(self::INPUT_FILTER);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreLabel()
    {
        return $this->_get(self::STORE_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules()
    {
        return $this->_get(self::VALIDATION_RULES);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultilineCount()
    {
        return $this->_get(self::MULTILINE_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function isVisible()
    {
        return $this->_get(self::VISIBLE);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->_get(self::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataModel()
    {
        return $this->_get(self::DATA_MODEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->_get(self::OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendClass()
    {
        return $this->_get(self::FRONTEND_CLASS);
    }

    /**
     * {@inheritdoc}
     */
    public function isUserDefined()
    {
        return $this->_get(self::USER_DEFINED);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendLabel()
    {
        return $this->_get(self::FRONTEND_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * {@inheritdoc}
     */
    public function isSystem()
    {
        return $this->_get(self::SYSTEM);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->_get(self::BACKEND_TYPE);
    }

    /**
     * Set attribute code
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Set frontend HTML for input element.
     *
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput)
    {
        return $this->setData(self::FRONTEND_INPUT, $frontendInput);
    }

    /**
     * Set template used for input (e.g. "date")
     *
     * @param string $inputFilter
     * @return $this
     */
    public function setInputFilter($inputFilter)
    {
        return $this->setData(self::INPUT_FILTER, $inputFilter);
    }

    /**
     * Set label of the store.
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel)
    {
        return $this->setData(self::STORE_LABEL, $storeLabel);
    }

    /**
     * Set validation rules.
     *
     * @param \Magento\Customer\Api\Data\ValidationRuleInterface[] $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules)
    {
        return $this->setData(self::VALIDATION_RULES, $validationRules);
    }

    /**
     * Set number of lines of the attribute value.
     *
     * @param int $multilineCount
     * @return $this
     */
    public function setMultilineCount($multilineCount)
    {
        return $this->setData(self::MULTILINE_COUNT, $multilineCount);
    }

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible)
    {
        return $this->setData(self::VISIBLE, $isVisible);
    }

    /**
     * Whether attribute is required.
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(self::REQUIRED, $isRequired);
    }

    /**
     * Get data model for attribute.
     *
     * @param string $dataModel
     * @return $this
     */
    public function setDataModel($dataModel)
    {
        return $this->setData(self::DATA_MODEL, $dataModel);
    }

    /**
     * Set options of the attribute (key => value pairs for select)
     *
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        return $this->setData(self::OPTIONS, $options);
    }

    /**
     * Set class which is used to display the attribute on frontend.
     *
     * @param string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass)
    {
        return $this->setData(self::FRONTEND_CLASS, $frontendClass);
    }

    /**
     * Set whether current attribute has been defined by a user.
     *
     * @param bool $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined)
    {
        return $this->setData(self::USER_DEFINED, $isUserDefined);
    }

    /**
     * Get attributes sort order.
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set label which supposed to be displayed on frontend.
     *
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel)
    {
        return $this->setData(self::FRONTEND_LABEL, $frontendLabel);
    }

    /**
     * Set the note attribute for the element.
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }

    /**
     * Set whether this is a system attribute.
     *
     * @param bool $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem)
    {
        return $this->setData(self::SYSTEM, $isSystem);
    }

    /**
     * Set backend type.
     *
     * @param string $backendType
     * @return $this
     */
    public function setBackendType($backendType)
    {
        return $this->setData(self::BACKEND_TYPE, $backendType);
    }

    /**
     * @inheritDoc
     */
    public function getIsUsedInGrid()
    {
        return $this->_get(self::IS_USED_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsVisibleInGrid()
    {
        return $this->_get(self::IS_VISIBLE_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsFilterableInGrid()
    {
        return $this->_get(self::IS_FILTERABLE_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function getIsSearchableInGrid()
    {
        return $this->_get(self::IS_SEARCHABLE_IN_GRID);
    }

    /**
     * @inheritDoc
     */
    public function setIsUsedInGrid($isUsedInGrid)
    {
        return $this->setData(self::IS_USED_IN_GRID, $isUsedInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsVisibleInGrid($isVisibleInGrid)
    {
        return $this->setData(self::IS_VISIBLE_IN_GRID, $isVisibleInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsFilterableInGrid($isFilterableInGrid)
    {
        return $this->setData(self::IS_FILTERABLE_IN_GRID, $isFilterableInGrid);
    }

    /**
     * @inheritDoc
     */
    public function setIsSearchableInGrid($isSearchableInGrid)
    {
        return $this->setData(self::IS_SEARCHABLE_IN_GRID, $isSearchableInGrid);
    }
}
