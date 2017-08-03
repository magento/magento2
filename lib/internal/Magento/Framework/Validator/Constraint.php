<?php
/**
 * Validator constraint delegates validation to wrapped validator.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

/**
 * @api
 * @since 2.0.0
 */
class Constraint extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Wrapped validator
     *
     * @var \Magento\Framework\Validator\ValidatorInterface
     * @since 2.0.0
     */
    protected $_wrappedValidator;

    /**
     * Alias can be used for search
     *
     * @var string
     * @since 2.0.0
     */
    protected $_alias;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Validator\ValidatorInterface $validator
     * @param string $alias
     * @since 2.0.0
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
     * @api
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @api
     * @since 2.0.0
     */
    public function getAlias()
    {
        return $this->_alias;
    }

    /**
     * Set translator to wrapped validator.
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\AbstractValidator
     * @since 2.0.0
     */
    public function setTranslator($translator = null)
    {
        $this->_wrappedValidator->setTranslator($translator);
        return $this;
    }

    /**
     * Get translator instance of wrapped validator
     *
     * @return \Magento\Framework\Translate\AdapterInterface|null
     * @since 2.0.0
     */
    public function getTranslator()
    {
        return $this->_wrappedValidator->getTranslator();
    }
}
