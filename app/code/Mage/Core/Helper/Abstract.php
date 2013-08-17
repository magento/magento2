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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract helper
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Mage_Core_Helper_Abstract
{
    /**
     * Helper module name
     *
     * @var string
     */
    protected $_moduleName;

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Layout model object
     *
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * Translator model
     *
     * @var Mage_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Mage_Core_Model_ModuleManager
     */
    private $_moduleManager;

    /**
     * @param Mage_Core_Helper_Context $context
     */
    public function __construct(Mage_Core_Helper_Context $context)
    {
        $this->_translator = $context->getTranslator();
        $this->_moduleManager = $context->getModuleManager();
    }

    /**
     * Retrieve request object
     *
     * @return Zend_Controller_Request_Http
     * @return Zend_Controller_Request_Http
     */
    protected function _getRequest()
    {
        if (!$this->_request) {
            $this->_request = Mage::getObjectManager()->get('Mage_Core_Controller_Request_Http');
        }
        return $this->_request;
    }

    /**
     * Loading cache data
     *
     * @param   string $cacheId
     * @return  mixed
     */
    protected function _loadCache($cacheId)
    {
        return Mage::app()->loadCache($cacheId);
    }

    /**
     * Saving cache
     *
     * @param mixed $data
     * @param string $cacheId
     * @param array $tags
     * @param bool $lifeTime
     * @return Mage_Core_Helper_Abstract
     */
    protected function _saveCache($data, $cacheId, $tags = array(), $lifeTime = false)
    {
        Mage::app()->saveCache($data, $cacheId, $tags, $lifeTime);
        return $this;
    }

    /**
     * Removing cache
     *
     * @param   string $cacheId
     * @return  Mage_Core_Helper_Abstract
     */
    protected function _removeCache($cacheId)
    {
        Mage::app()->removeCache($cacheId);
        return $this;
    }

    /**
     * Cleaning cache
     *
     * @param   array $tags
     * @return  Mage_Core_Helper_Abstract
     */
    protected function _cleanCache($tags=array())
    {
        Mage::app()->cleanCache($tags);
        return $this;
    }

    /**
     * Retrieve helper module name
     *
     * @return string
     */
    protected function _getModuleName()
    {
        if (!$this->_moduleName) {
            $class = get_class($this);
            $this->_moduleName = substr($class, 0, strpos($class, '_Helper'));
        }
        return $this->_moduleName;
    }

    /**
     * Check whether or not the module output is enabled in Configuration
     *
     * @param string $moduleName Full module name
     * @return boolean
     * @deprecated use Mage_Core_Model_ModuleManager::isOutputEnabled()
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }
        return $this->_moduleManager->isOutputEnabled($moduleName);
    }

    /**
     * Check is module exists and enabled in global config.
     *
     * @param string $moduleName the full module name, example Mage_Core
     * @return boolean
     * @deprecated use Mage_Core_Model_ModuleManager::isEnabled()
     */
    public function isModuleEnabled($moduleName = null)
    {
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }
        return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * Translate
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_getModuleName());
        array_unshift($args, $expr);
        return $this->_translator->translate($args);
    }

    /**
     * Escape html entities
     *
     * @param   string|array $data
     * @param   array $allowedTags
     * @return  mixed
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item);
            }
        } else {
            // process single item
            if (strlen($data)) {
                if (is_array($allowedTags) and !empty($allowedTags)) {
                    $allowed = implode('|', $allowedTags);
                    $result = preg_replace('/<([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)>/si', '##$1$2$3##', $data);
                    $result = htmlspecialchars($result, ENT_COMPAT, 'UTF-8', false);
                    $result = preg_replace('/##([\/\s\r\n]*)(' . $allowed . ')([\/\s\r\n]*)##/si', '<$1$2$3>', $result);
                } else {
                    $result = htmlspecialchars($data, ENT_COMPAT, 'UTF-8', false);
                }
            } else {
                $result = $data;
            }
        }
        return $result;
    }

    /**
     * Remove html tags, but leave "<" and ">" signs
     *
     * @param string $html
     * @return string
     */
    public function removeTags($html)
    {
        $html = preg_replace("# <(?![/a-z]) | (?<=\s)>(?![a-z]) #exi", "htmlentities('$0')", $html);
        $html =  strip_tags($html);
        return htmlspecialchars_decode($html);
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $escape
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $escape = false)
    {
        $result = strip_tags($data, $allowableTags);
        return $escape ? $this->escapeHtml($result, $allowableTags) : $result;
    }

    /**
     * Escape html entities in url
     *
     * @param string $data
     * @return string
     */
    public function escapeUrl($data)
    {
        return htmlspecialchars($data);
    }

    /**
     * Escape quotes in java script
     *
     * @param mixed $data
     * @param string $quote
     * @return mixed
     */
    public function jsQuoteEscape($data, $quote = '\'')
    {
        if (is_array($data)) {
            $result = array();
            foreach ($data as $item) {
                $result[] = str_replace($quote, '\\' . $quote, $item);
            }
            return $result;
        }
        return str_replace($quote, '\\' . $quote, $data);
    }

    /**
     * Escape quotes inside html attributes
     * Use $addSlashes = false for escaping js that inside html attribute (onClick, onSubmit etc)
     *
     * @param string $data
     * @param bool $addSlashes
     * @return string
     */
    public function quoteEscape($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data);
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function _getUrl($route, $params = array())
    {
        return Mage::getUrl($route, $params);
    }

    /**
     * Declare layout
     *
     * @param   Mage_Core_Model_Layout $layout
     * @return  Mage_Core_Helper_Abstract
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Retrieve layout model object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * base64_encode() for URLs encoding
     *
     * @param    string $url
     * @return   string
     */
    public function urlEncode($url)
    {
        return strtr(base64_encode($url), '+/=', '-_,');
    }

    /**
     *  base64_decode() for URLs decoding
     *
     * @param    string $url
     * @return   string
     */
    public function urlDecode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        /** @var $urlModel Mage_Core_Model_Url */
        $urlModel = Mage::getSingleton('Mage_Core_Model_Url');
        return $urlModel->sessionUrlVar($url);
    }

    /**
     *   Translate array
     *
     * @param    array $arr
     * @return   array
     */
    public function translateArray($arr = array())
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $v = self::translateArray($v);
            } elseif ($k === 'label') {
                $v = self::__($v);
            }
            $arr[$k] = $v;
        }
        return $arr;
    }
}
