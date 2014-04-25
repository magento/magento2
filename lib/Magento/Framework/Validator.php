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

namespace Magento\Framework;

/**
 * Validator class that represents chain of validators.
 */
class Validator extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Validator chain
     *
     * @var array
     */
    protected $_validators = array();

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
        $this->_validators[] = array(
            'instance' => $validator,
            'breakChainOnFailure' => (bool)$breakChainOnFailure
        );
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
