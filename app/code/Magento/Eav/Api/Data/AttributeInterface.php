<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Retrieve code of the attribute.
     *
     * @return string
     */
    public function getAttributeCode();

    /**
     * Frontend HTML for input element.
     *
     * @return string
     */
    public function getFrontendInput();

    /**
     * Retrieve entity type id
     *
     * @return string|null
     */
    public function getEntityTypeId();

    /**
     * Whether attribute is required.
     *
     * @return bool
     */
    public function getIsRequired();

    /**
     * Return options of the attribute (key => value pairs for select)
     *
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]|null
     */
    public function getOptions();

    /**
     * Whether current attribute has been defined by a user.
     *
     * @return bool|null
     */
    public function getIsUserDefined();

    /**
     * Return frontend label for each store
     *
     * @return string|null
     */
    public function getDefaultFrontendLabel();

    /**
     * Return frontend label for each store
     *
     * @return \Magento\Eav\Api\Data\AttributeFrontendLabelInterface[]
     */
    public function getFrontendLabels();

    /**
     * Get the note attribute for the element.
     *
     * @return string|null
     */
    public function getNote();

    /**
     * Get backend type.
     *
     * @return string|null
     */
    public function getBackendType();

    /**
     * Get backend model
     *
     * @return string|null
     */
    public function getBackendModel();

    /**
     * Get source model
     *
     * @return string|null
     */
    public function getSourceModel();

    /**
     * Get default value for the element.
     *
     * @return string|null
     */
    public function getDefaultValue();

    /**
     * Whether this is a unique attribute
     *
     * @return string|null
     */
    public function getIsUnique();

    /**
     * Retrieve frontend class of attribute
     *
     * @return string|null
     */
    public function getFrontendClass();

    /**
     * Retrieve validation rules.
     *
     * @return \Magento\Eav\Api\Data\AttributeValidationRuleInterface[]|null
     */
    public function getValidationRules();
}
