<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api\Data;

/**
 * Customer attribute metadata interface.
 * @api
 */
interface AttributeMetadataInterface extends \Magento\Framework\Api\MetadataObjectInterface
{
    /**#@+
     * Constants used as keys of data array
     */
    const ATTRIBUTE_CODE = 'attribute_code';
    const FRONTEND_INPUT = 'frontend_input';
    const INPUT_FILTER = 'input_filter';
    const STORE_LABEL = 'store_label';
    const VALIDATION_RULES = 'validation_rules';
    const OPTIONS = 'options';
    const VISIBLE = 'visible';
    const REQUIRED = 'required';
    const MULTILINE_COUNT = 'multiline_count';
    const DATA_MODEL = 'data_model';
    const USER_DEFINED = 'user_defined';
    const FRONTEND_CLASS = 'frontend_class';
    const SORT_ORDER = 'sort_order';
    const FRONTEND_LABEL = 'frontend_label';
    const SYSTEM = 'system';
    const NOTE = 'note';
    const BACKEND_TYPE = 'backend_type';
    const IS_USED_IN_GRID = 'is_used_in_grid';
    const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';
    const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';
    const IS_SEARCHABLE_IN_GRID = 'is_searchable_in_grid';
    /**#@-*/

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput();

    /**
     * Set frontend HTML for input element.
     *
     * @param string $frontendInput
     * @return $this
     */
    public function setFrontendInput($frontendInput);

    /**
     * Get template used for input (e.g. "date")
     *
     * @return string
     */
    public function getInputFilter();

    /**
     * Set template used for input (e.g. "date")
     *
     * @param string $inputFilter
     * @return $this
     */
    public function setInputFilter($inputFilter);

    /**
     * Get label of the store.
     *
     * @return string
     */
    public function getStoreLabel();

    /**
     * Set label of the store.
     *
     * @param string $storeLabel
     * @return $this
     */
    public function setStoreLabel($storeLabel);

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Customer\Api\Data\ValidationRuleInterface[]
     */
    public function getValidationRules();

    /**
     * Set validation rules.
     *
     * @param \Magento\Customer\Api\Data\ValidationRuleInterface[] $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules);

    /**
     * Number of lines of the attribute value.
     *
     * @return int
     */
    public function getMultilineCount();

    /**
     * Set number of lines of the attribute value.
     *
     * @param int $multilineCount
     * @return $this
     */
    public function setMultilineCount($multilineCount);

    /**
     * Whether attribute is visible on frontend.
     *
     * @return bool
     */
    public function isVisible();

    /**
     * Set whether attribute is visible on frontend.
     *
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisible($isVisible);

    /**
     * Whether attribute is required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Set whether attribute is required.
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired);

    /**
     * Get data model for attribute.
     *
     * @return string
     */
    public function getDataModel();

    /**
     * Get data model for attribute.
     *
     * @param string $dataModel
     * @return $this
     */
    public function setDataModel($dataModel);

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Customer\Api\Data\OptionInterface[]
     */
    public function getOptions();

    /**
     * Set options of the attribute (key => value pairs for select)
     *
     * @param \Magento\Customer\Api\Data\OptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null);

    /**
     * Get class which is used to display the attribute on frontend.
     *
     * @return string
     */
    public function getFrontendClass();

    /**
     * Set class which is used to display the attribute on frontend.
     *
     * @param string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass);

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool
     */
    public function isUserDefined();

    /**
     * Set whether current attribute has been defined by a user.
     *
     * @param bool $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined);

    /**
     * Get attributes sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get attributes sort order.
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get label which supposed to be displayed on frontend.
     *
     * @return string
     */
    public function getFrontendLabel();

    /**
     * Set label which supposed to be displayed on frontend.
     *
     * @param string $frontendLabel
     * @return $this
     */
    public function setFrontendLabel($frontendLabel);

    /**
     * Get the note attribute for the element.
     *
     * @return string
     */
    public function getNote();

    /**
     * Set the note attribute for the element.
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note);

    /**
     * Whether this is a system attribute.
     *
     * @return bool
     */
    public function isSystem();

    /**
     * Set whether this is a system attribute.
     *
     * @param bool $isSystem
     * @return $this
     */
    public function setIsSystem($isSystem);

    /**
     * Get backend type.
     *
     * @return string
     */
    public function getBackendType();

    /**
     * Set backend type.
     *
     * @param string $backendType
     * @return $this
     */
    public function setBackendType($backendType);

    /**
     * Whether it is used in customer grid
     *
     * @return bool|null
     */
    public function getIsUsedInGrid();

    /**
     * Whether it is visible in customer grid
     *
     * @return bool|null
     */
    public function getIsVisibleInGrid();

    /**
     * Whether it is filterable in customer grid
     *
     * @return bool|null
     */
    public function getIsFilterableInGrid();

    /**
     * Whether it is searchable in customer grid
     *
     * @return bool|null
     */
    public function getIsSearchableInGrid();

    /**
     * Set whether it is used in customer grid
     *
     * @param bool $isUsedInGrid
     * @return $this
     */
    public function setIsUsedInGrid($isUsedInGrid);

    /**
     * Set whether it is visible in customer grid
     *
     * @param bool $isVisibleInGrid
     * @return $this
     */
    public function setIsVisibleInGrid($isVisibleInGrid);

    /**
     * Set whether it is filterable in customer grid
     *
     * @param bool $isFilterableInGrid
     * @return $this
     */
    public function setIsFilterableInGrid($isFilterableInGrid);

    /**
     * Set whether it is searchable in customer grid
     *
     * @param bool $isSearchableInGrid
     * @return $this
     */
    public function setIsSearchableInGrid($isSearchableInGrid);
}
