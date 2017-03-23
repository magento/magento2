<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeInterface
 * @api
 */
interface AttributeInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    const ATTRIBUTE_ID = 'attribute_id';

    const IS_UNIQUE = 'is_unique';

    const SCOPE = 'scope';

    const FRONTEND_CLASS = 'frontend_class';

    const ATTRIBUTE_CODE = 'attribute_code';

    const FRONTEND_INPUT = 'frontend_input';

    const IS_REQUIRED = 'is_required';

    const OPTIONS = 'options';

    const IS_USER_DEFINED = 'is_user_defined';

    const FRONTEND_LABEL = 'frontend_label';

    const FRONTEND_LABELS = 'frontend_labels';

    const NOTE = 'note';

    const BACKEND_TYPE = 'backend_type';

    const BACKEND_MODEL = 'backend_model';

    const SOURCE_MODEL = 'source_model';

    const VALIDATE_RULES = 'validate_rules';

    const ENTITY_TYPE_ID = 'entity_type_id';

    /**
     * Retrieve id of the attribute.
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Set id of the attribute.
     *
     * @param int $attributeId
     * @return $this
     */
    public function setAttributeId($attributeId);

    /**
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Set code of the attribute.
     *
     * @param string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode);

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
     * Retrieve entity type id
     *
     * @return string|null
     */
    public function getEntityTypeId();

    /**
     * Set entity type id
     *
     * @param string $entityTypeId
     * @return $this
     */
    public function setEntityTypeId($entityTypeId);

    /**
     * Whether attribute is required.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsRequired();

    /**
     * Set whether attribute is required.
     *
     * @param bool $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired);

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     */
    public function getOptions();

    /**
     * Set options of the attribute (key => value pairs for select)
     *
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface[] $options
     * @return $this
     */
    public function setOptions(array $options = null);

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool|null
     */
    public function getIsUserDefined();

    /**
     * Set whether current attribute has been defined by a user.
     *
     * @param bool $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined);

    /**
     * Return frontend label for default store
     *
     * @return string|null
     */
    public function getDefaultFrontendLabel();

    /**
     * Set frontend label for default store
     *
     * @param string $defaultFrontendLabel
     * @return $this
     */
    public function setDefaultFrontendLabel($defaultFrontendLabel);

    /**
     * Return frontend label for each store
     *
     * @return \Magento\Eav\Api\Data\AttributeFrontendLabelInterface[]
     */
    public function getFrontendLabels();

    /**
     * Set frontend label for each store
     *
     * @param \Magento\Eav\Api\Data\AttributeFrontendLabelInterface[] $frontendLabels
     * @return $this
     */
    public function setFrontendLabels(array $frontendLabels = null);

    /**
     * Get the note attribute for the element.
     *
     * @return string|null
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
     * Get backend type.
     *
     * @return string|null
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
     * Get backend model
     *
     * @return string|null
     */
    public function getBackendModel();

    /**
     * Set backend model
     *
     * @param string $backendModel
     * @return $this
     */
    public function setBackendModel($backendModel);

    /**
     * Get source model
     *
     * @return string|null
     */
    public function getSourceModel();

    /**
     * Set source model
     *
     * @param string $sourceModel
     * @return $this
     */
    public function setSourceModel($sourceModel);

    /**
     * Get default value for the element.
     *
     * @return string|null
     */
    public function getDefaultValue();

    /**
     * Set default value for the element.
     *
     * @param string $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue);

    /**
     * Whether this is a unique attribute
     *
     * @return string|null
     */
    public function getIsUnique();

    /**
     * Set whether this is a unique attribute
     *
     * @param string $isUnique
     * @return $this
     */
    public function setIsUnique($isUnique);

    /**
     * Retrieve frontend class of attribute
     *
     * @return string|null
     */
    public function getFrontendClass();

    /**
     * Set frontend class of attribute
     *
     * @param string $frontendClass
     * @return $this
     */
    public function setFrontendClass($frontendClass);

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Eav\Api\Data\AttributeValidationRuleInterface[]|null
     */
    public function getValidationRules();

    /**
     * Set validation rules.
     *
     * @param \Magento\Eav\Api\Data\AttributeValidationRuleInterface[] $validationRules
     * @return $this
     */
    public function setValidationRules(array $validationRules = null);

    /**
     * @return \Magento\Eav\Api\Data\AttributeExtensionInterface|null
     */
    public function getExtensionAttributes();
}
