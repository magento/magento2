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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Validator for check not protected file extensions
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_File_Validator_NotProtectedExtension extends Zend_Validate_Abstract
{
    const PROTECTED_EXTENSION = 'protectedExtension';

    /**
     * The file extension
     *
     * @var string
     */
    protected $_value;

    /**
     * Protected file types
     *
     * @var array
     */
    protected $_protectedFileExtensions = array();

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_initMessageTemplates();
        $this->_initProtectedFileExtensions();
    }

    /**
     * Initialize message templates with translating
     *
     * @return Mage_Core_Model_File_Validator_NotProtectedExtension
     */
    protected function _initMessageTemplates()
    {
        if (!$this->_messageTemplates) {
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');
            $this->_messageTemplates = array(
                self::PROTECTED_EXTENSION =>
                    $helper->__('File with an extension "%value%" is protected and cannot be uploaded'),
            );
        }
        return $this;
    }

    /**
     * Initialize protected file extensions
     *
     * @return Mage_Core_Model_File_Validator_NotProtectedExtension
     */
    protected function _initProtectedFileExtensions()
    {
        if (!$this->_protectedFileExtensions) {
            /** @var $helper Mage_Core_Helper_Data */
            $helper = Mage::helper('Mage_Core_Helper_Data');
            $extensions = $helper->getProtectedFileExtensions();
            if (is_string($extensions)) {
                $extensions = explode(',', $extensions);
            }
            foreach ($extensions as &$ext) {
                $ext = strtolower(trim($ext));
            }
            $this->_protectedFileExtensions = (array) $extensions;
        }
        return $this;
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
