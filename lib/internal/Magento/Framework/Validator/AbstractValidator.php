<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\Translator\TranslatorInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Abstract validator class.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractValidator implements ValidatorInterface, ResetAfterRequestInterface
{
    /**
     * @var TranslatorInterface|null
     */
    protected static $_defaultTranslator = null;

    /**
     * @var TranslatorInterface|null
     */
    protected $_translator = null;

    /**
     * @var array
     */
    protected $_messages = [];

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_translator = null;
        $this->_messages = [];
    }

    /**
     * Set default translator instance
     *
     * @param TranslatorInterface|null $translator
     * @return void
     */
    public static function setDefaultTranslator(?TranslatorInterface $translator = null)
    {
        self::$_defaultTranslator = $translator;
    }

    /**
     * Get default translator
     *
     * @return TranslatorInterface|null
     */
    public static function getDefaultTranslator()
    {
        return self::$_defaultTranslator;
    }

    /**
     * Set translator instance
     *
     * @param TranslatorInterface|null $translator
     * @return AbstractValidator
     */
    public function setTranslator(?TranslatorInterface $translator = null)
    {
        $this->_translator = $translator;
        return $this;
    }

    /**
     * Get translator
     *
     * @return TranslatorInterface|null
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
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * Whether it has failure messages
     *
     * @return bool
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
