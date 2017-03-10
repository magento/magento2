<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Export;

/**
 * Class DefinitionConfigFieldList contains a list of configuration fields
 * that are marked as sensitive fields or as specific environment variables.
 */
class DefinitionConfigFieldList
{
    /**
     * Used to designate the configuration fields as sensitive fields.
     */
    const SENSITIVE_FIELDS = 'sensitiveFields';

    /**
     * Used to designate the configuration fields as a specific environment variables.
     */
    const ENV_SPECIFIC_VARIABLES = 'envSpecificVariables';

    /**
     * List of configuration fields marked as sensitive fields.
     *
     * @var array
     */
    private $sensitiveFieldList;

    /**
     * List of configuration fields marked as specific environment variables.
     *
     * @var array
     */
    private $envSpecificVariableList;

    /**
     * DefinitionList constructor.
     *
     * @param array $sensitiveFieldList list of configuration fields marked as sensitive fields
     * @param array $envSpecificVariableList list of configuration fields marked as specific environment variables
     */
    public function __construct(array $sensitiveFieldList = [], array $envSpecificVariableList = [])
    {
        $this->sensitiveFieldList = $sensitiveFieldList;
        $this->envSpecificVariableList = $envSpecificVariableList;
    }

    /**
     * Checks whether the configuration field belongs to the specified type.
     *
     * @param string $configField configuration field
     * @param string $type type of configuration field to be checked
     * @return bool
     */
    public function belongsTo($configField, $type)
    {
        $configFieldList = $this->getByType($type);
        return in_array($configField, $configFieldList);
    }

    /**
     * Checks whether the configuration field is present in the lists.
     *
     * @param string $configField configuration field
     * @return bool
     */
    public function isPresent($configField)
    {
        return in_array($configField, $this->getAll());
    }

    /**
     * Gets a list of configuration fields for the specified type.
     *
     * @param string $type type of configuration fields
     * @return array
     */
    private function getByType($type)
    {
        switch ($type) {
            case self::SENSITIVE_FIELDS:
                $configFieldList = $this->sensitiveFieldList;
                break;
            case self::ENV_SPECIFIC_VARIABLES:
                $configFieldList = $this->envSpecificVariableList;
                break;
            default:
                $configFieldList = [];
        }

        return array_keys(array_filter(
            $configFieldList,
            function ($value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        ));
    }

    /**
     * Returns all configuration fields than was marked as sensitive fields or special environment variables.
     *
     * @return array
     */
    private function getAll()
    {
        return array_unique(array_merge(
            $this->getByType(self::ENV_SPECIFIC_VARIABLES),
            $this->getByType(self::SENSITIVE_FIELDS)
        ));
    }
}
