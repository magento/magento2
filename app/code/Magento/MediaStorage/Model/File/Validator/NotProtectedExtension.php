<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\MediaStorage\Model\File\Validator;

/**
 * Validator for check not protected file extensions
 */
class NotProtectedExtension extends \Zend_Validate_Abstract
{
    /**
     * Protected extension message key
     */
    const PROTECTED_EXTENSION = 'protectedExtension';

    /**
     * Protected files config path
     */
    const XML_PATH_PROTECTED_FILE_EXTENSIONS = 'general/file/protected_extensions';

    /**
     * The file extension
     *
     * @var string
     */
    protected $_value;

    /**
     * Protected file types
     *
     * @var string[]
     */
    protected $_protectedFileExtensions = [];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Init validator
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_initMessageTemplates();
        $this->_initProtectedFileExtensions();
    }

    /**
     * Initialize message templates with translating
     *
     * @return $this
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            $this->_messageTemplates = [
                self::PROTECTED_EXTENSION => __('File with an extension "%value%" is protected and cannot be uploaded'),
            ];
        }
        return $this;
    }

    /**
     * Initialize protected file extensions
     *
     * @return $this
     */
    protected function _initProtectedFileExtensions()
    {
        if (!$this->_protectedFileExtensions) {
            $extensions = $this->getProtectedFileExtensions();
            if (is_string($extensions)) {
                $extensions = explode(',', $extensions);
            }
            foreach ($extensions as &$ext) {
                $ext = strtolower(trim($ext));
            }
            $this->_protectedFileExtensions = (array)$extensions;
        }
        return $this;
    }

    /**
     * Return list with protected file extensions
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return string|string[]
     */
    public function getProtectedFileExtensions($store = null)
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_PROTECTED_FILE_EXTENSIONS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param string $value         Extension of file
     * @return bool
     */
    public function isValid($value)
    {
        $value = strtolower(trim($value));
        $this->_setValue($value);

        if (in_array($this->_value, $this->_protectedFileExtensions)) {
            $this->_error(self::PROTECTED_EXTENSION, $this->_value);
            return false;
        }

        return true;
    }
}
