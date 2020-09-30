<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Validator encapsulates multiple validation rules for \Magento\Framework\DataObject.
 * Able to validate both individual fields and a whole object.
 */
namespace Magento\Framework\Validator;

/**
 * @api
 * @since 100.0.2
 */
class DataObject implements \Zend_Validate_Interface
{
    /**
     * Validation rules per scope (particular fields or entire entity)
     *
     * @var \Zend_Validate_Interface[]
     */
    private $_rules = [];

    /**
     * Validation error messages
     *
     * @var array
     */
    private $_messages = [];

    /**
     * Add rule to be applied to a validation scope
     *
     * @param \Zend_Validate_Interface $validator
     * @param string $fieldName Field name to apply validation to, or empty value to validate entity as a whole
     * @return \Magento\Framework\Validator\DataObject
     * @api
     */
    public function addRule(\Zend_Validate_Interface $validator, $fieldName = '')
    {
        if (!array_key_exists($fieldName, $this->_rules)) {
            $this->_rules[$fieldName] = $validator;
        } else {
            $existingValidator = $this->_rules[$fieldName];
            if (!$existingValidator instanceof \Zend_Validate) {
                $compositeValidator = new \Zend_Validate();
                $compositeValidator->addValidator($existingValidator);
                $this->_rules[$fieldName] = $compositeValidator;
            }
            $this->_rules[$fieldName]->addValidator($validator);
        }
        return $this;
    }

    /**
     * Check whether the entity is valid according to defined validation rules
     *
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     *
     * @throws \Exception
     * @api
     */
    public function isValid($entity)
    {
        $this->_messages = [];
        /** @var $validator \Zend_Validate_Interface */
        foreach ($this->_rules as $fieldName => $validator) {
            $value = $fieldName ? $entity->getDataUsingMethod($fieldName) : $entity;
            if (!$validator->isValid($value)) {
                $this->_messages = array_merge($this->_messages, array_values($validator->getMessages()));
            }
        }
        return empty($this->_messages);
    }

    /**
     * Return error messages (if any) after the last validation
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
