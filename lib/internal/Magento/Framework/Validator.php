<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework;

/**
 * Validator class that represents chain of validators.
 *
 * @api
 * @since 100.0.2
 */
class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Validator chain
     *
     * @var array
     */
    protected $_validators = [];

    /**
     * Adds a validator to the end of the chain
     *
     * @param \Magento\Framework\Validator\ValidatorInterface $validator
     * @param boolean $breakChainOnFailure
     * @return \Magento\Framework\Validator
     */
    public function addValidator(
        \Magento\Framework\Validator\ValidatorInterface $validator,
        $breakChainOnFailure = false
    ) {
        if (!$validator->hasTranslator()) {
            $validator->setTranslator($this->getTranslator());
        }
        $this->_validators[] = [
            'instance' => $validator,
            'breakChainOnFailure' => (bool)$breakChainOnFailure,
        ];
        return $this;
    }

    /**
     * Returns true if and only if $value passes all validations in the chain
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $result = true;
        $this->_clearMessages();

        /** @var $validator \Zend_Validate_Interface */
        foreach ($this->_validators as $element) {
            $validator = $element['instance'];
            if ($validator->isValid($value)) {
                continue;
            }
            $result = false;
            $this->_addMessages($validator->getMessages());
            if ($element['breakChainOnFailure']) {
                break;
            }
        }

        return $result;
    }

    /**
     * Set translator to chain.
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\AbstractValidator
     */
    public function setTranslator($translator = null)
    {
        foreach ($this->_validators as $validator) {
            $validator['instance']->setTranslator($translator);
        }
        return parent::setTranslator($translator);
    }
}
