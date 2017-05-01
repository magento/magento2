<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

/**
 * Abstract validator class.
 *
 * @api
 */
abstract class AbstractValidator implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * @var \Magento\Framework\Translate\AdapterInterface|null
     */
    protected static $_defaultTranslator = null;

    /**
     * @var \Magento\Framework\Translate\AdapterInterface|null
     */
    protected $_translator = null;

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_messages = [];

    /**
     * Set default translator instance
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return void
     * @api
     */
    public static function setDefaultTranslator(\Magento\Framework\Translate\AdapterInterface $translator = null)
    {
        self::$_defaultTranslator = $translator;
    }

    /**
     * Get default translator
     *
     * @return \Magento\Framework\Translate\AdapterInterface|null
     * @api
     */
    public static function getDefaultTranslator()
    {
        return self::$_defaultTranslator;
    }

    /**
     * Set translator instance
     *
     * @param \Magento\Framework\Translate\AdapterInterface|null $translator
     * @return \Magento\Framework\Validator\AbstractValidator
     */
    public function setTranslator($translator = null)
    {
        $this->_translator = $translator;
        return $this;
    }

    /**
     * Get translator
     *
     * @return \Magento\Framework\Translate\AdapterInterface|null
     */
    public function getTranslator()
    {
        if ($this->_translator === null) {
            return self::getDefaultTranslator();
        }
        return $this->_translator;
    }

    /**
     * Check that translator is set.
     *
     * @return boolean
     */
    public function hasTranslator()
    {
        return $this->_translator !== null;
    }

    /**
     * Get validation failure messages
     *
     * @return string[]
     * @api
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Whether it has failure messages
     *
     * @return bool
     * @api
     */
    public function hasMessages()
    {
        return !empty($this->_messages);
    }

    /**
     * Clear messages
     *
     * @return void
     */
    protected function _clearMessages()
    {
        $this->_messages = [];
    }

    /**
     * Add messages
     *
     * @param array $messages
     * @return void
     */
    protected function _addMessages(array $messages)
    {
        $this->_messages = array_merge_recursive($this->_messages, $messages);
    }
}
