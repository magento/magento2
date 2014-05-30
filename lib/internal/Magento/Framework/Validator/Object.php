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

/**
 * Validator encapsulates multiple validation rules for \Magento\Framework\Object.
 * Able to validate both individual fields and a whole object.
 */
namespace Magento\Framework\Validator;

class Object implements \Zend_Validate_Interface
{
    /**
     * Validation rules per scope (particular fields or entire entity)
     *
     * @var \Zend_Validate_Interface[]
     */
    private $_rules = array();

    /**
     * Validation error messages
     *
     * @var array
     */
    private $_messages = array();

    /**
     * Add rule to be applied to a validation scope
     *
     * @param \Zend_Validate_Interface $validator
     * @param string $fieldName Field name to apply validation to, or empty value to validate entity as a whole
     * @return \Magento\Framework\Validator\Object
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
     * @param \Magento\Framework\Object $entity
     * @return bool
     *
     * @throws \Exception
     */
    public function isValid($entity)
    {
        $this->_messages = array();
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
