<?php
/**
 * Validator constraint delegates validation to wrapped validator.
 *
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
namespace Magento\Framework\Validator;

class Constraint extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * Wrapped validator
     *
     * @var \Magento\Framework\Validator\ValidatorInterface
     */
    protected $_wrappedValidator;

    /**
     * Alias can be used for search
     *
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
            $value = new \Magento\Framework\Object($value);
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
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\AbstractValidator
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
     */
    public function getTranslator()
    {
        return $this->_wrappedValidator->getTranslator();
    }
}
