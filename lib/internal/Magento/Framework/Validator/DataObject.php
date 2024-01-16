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

use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;

/**
 * @api
 * @since 100.0.2
 */
class DataObject implements ValidatorInterface
{
    /**
     * Validation rules per scope (particular fields or entire entity)
     *
     * @var ValidatorInterface[]
     */
    private $_rules = [];

    /**
     * @var array
     */
    private $_messages = [];

    /**
     * Add rule to be applied to a validation scope
     *
     * @param ValidatorInterface $validator
     * @param string $fieldName Field name to apply validation to, or empty value to validate entity as a whole
     * @return \Magento\Framework\Validator\DataObject
     */
    public function addRule(ValidatorInterface $validator, $fieldName = '')
    {
        if (!array_key_exists($fieldName, $this->_rules)) {
            $this->_rules[$fieldName] = $validator;
        } else {
            $existingValidator = $this->_rules[$fieldName];
            if (!$existingValidator instanceof ValidatorChain) {
                $compositeValidator = new ValidatorChain();
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
     */
    public function isValid($entity)
    {
        $this->_messages = [];
        /** @var $validator \Laminas\Validator\ValidatorInterface */
        foreach ($this->_rules as $fieldName => $validator) {
            $value = $fieldName ? $entity->getDataUsingMethod($fieldName) : $entity;
            if (!$validator->isValid($value)) {
                $this->_messages[] =  array_values($validator->getMessages());
            }
        }
        $this->_messages = array_merge([], ...$this->_messages);
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
