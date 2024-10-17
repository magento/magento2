<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\Translator\TranslatorInterface;

/**
 * Validator constraint delegates validation to wrapped validator.
 *
 * @api
 * @since 100.0.2
 */
class Constraint extends AbstractValidator
{
    /**
     * @var \Magento\Framework\Validator\ValidatorInterface
     */
    protected $_wrappedValidator;

    /**
     * @var string
     */
    protected $_alias;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Validator\ValidatorInterface $validator
     * @param string $alias
     */
    public function __construct(\Magento\Framework\Validator\ValidatorInterface $validator, $alias = null)
    {
        $this->_wrappedValidator = $validator;
        $this->_alias = $alias;
    }

    /**
     * Delegate validation to wrapped validator
     *
     * @param mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $result = true;
        $this->_clearMessages();

        if (!$this->_wrappedValidator->isValid($this->_getValidatorValue($value))) {
            $this->_addMessages($this->_wrappedValidator->getMessages());
            $result = false;
        }

        return $result;
    }

    /**
     * Get value that should be validated.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function _getValidatorValue($value)
    {
        if (is_array($value)) {
            $value = new \Magento\Framework\DataObject($value);
        }
        return $value;
    }

    /**
     * Get constraint alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Set translator to wrapped validator.
     *
     * @param TranslatorInterface|null $translator
     * @return AbstractValidator
     */
    public function setTranslator(?TranslatorInterface $translator = null)
    {
        $this->_wrappedValidator->setTranslator($translator);
        return $this;
    }

    /**
     * Get translator instance of wrapped validator
     *
     * @return TranslatorInterface|null
     */
    public function getTranslator()
    {
        return $this->_wrappedValidator->getTranslator();
    }
}
