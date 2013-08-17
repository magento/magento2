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
 * Core Email Template Filter Model
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Email_Template_Filter extends Varien_Filter_Template
{
    /**
     * Use absolute links flag
     *
     * @var bool
     */
    protected $_useAbsoluteLinks = false;

    /**
     * Whether to allow SID in store directive: NO
     *
     * @var bool
     */
    protected $_useSessionInUrl = false;

    /**
     * Modifier Callbacks
     *
     * @var array
     */
    protected $_modifiers = array('nl2br'  => '');

    protected $_storeId = null;

    protected $_plainTemplateMode = false;

    /**
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * Setup callbacks for filters
     *
     * @param Mage_Core_Model_View_Url $viewUrl
     */
    public function __construct(Mage_Core_Model_View_Url $viewUrl)
    {
        $this->_viewUrl = $viewUrl;
        $this->_modifiers['escape'] = array($this, 'modifierEscape');
    }

    /**
     * Set use absolute links flag
     *
     * @param bool $flag
     * @return Mage_Core_Model_Email_Template_Filter
     */
    public function setUseAbsoluteLinks($flag)
    {
        $this->_useAbsoluteLinks = $flag;
        return $this;
    }

    /**
     * Setter whether SID is allowed in store directive
     * Doesn't set anything intentionally, since SID is not allowed in any kind of emails
     *
     * @param bool $flag
     * @return Mage_Core_Model_Email_Template_Filter
     */
    public function setUseSessionInUrl($flag)
    {
        $this->_useSessionInUrl = $flag;
        return $this;
    }

    /**
     * Setter
     *
     * @param boolean $plainTemplateMode
     * @return Mage_Core_Model_Email_Template_Filter
     */
    public function setPlainTemplateMode($plainTemplateMode)
    {
        $this->_plainTemplateMode = (bool)$plainTemplateMode;
        return $this;
    }

    /**
     * Getter
     *
     * @return boolean
     */
    public function getPlainTemplateMode()
    {
        return $this->_plainTemplateMode;
    }

    /**
     * Setter
     *
     * @param integer $storeId
     * @return Mage_Core_Model_Email_Template_Filter
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Getter
     * if $_storeId is null return Design store id
     *
     * @return integer
     */
    public function getStoreId()
    {
        if (null === $this->_storeId) {
            $this->_storeId = Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve Block html directive
     *
     * @param array $construction
     * @return string
     */
    public function blockDirective($construction)
    {
        $skipParams = array('type', 'id', 'output');
        $blockParameters = $this->_getIncludeParameters($construction[2]);
        $layout = Mage::app()->getLayout();

        if (isset($blockParameters['type'])) {
            $type = $blockParameters['type'];
            $block = $layout->createBlock($type, null, array('data' => $blockParameters));
        } elseif (isset($blockParameters['id'])) {
            $block = $layout->createBlock('Mage_Cms_Block_Block');
            if ($block) {
                $block->setBlockId($blockParameters['id']);
            }
        }

        if ($block) {
            $block->setBlockParams($blockParameters);
            foreach ($blockParameters as $k => $v) {
                if (in_array($k, $skipParams)) {
                    continue;
                }
                $block->setDataUsingMethod($k, $v);
            }
        }

        if (!$block) {
            return '';
        }
        if (isset($blockParameters['output'])) {
            $method = $blockParameters['output'];
        }
        if (!isset($method) || !is_string($method) || !method_exists($block, $method)) {
            $method = 'toHtml';
        }
        return $block->$method();
    }

    /**
     * Retrieve layout html directive
     *
     * @param array $construction
     * @return string
     */
    public function layoutDirective($construction)
    {
        $skipParams = array('handle', 'area');

        $params = $this->_getIncludeParameters($construction[2]);
        $layoutParams = array();
        if (isset($params['area'])) {
            $layoutParams['area'] = $params['area'];
        }
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout', $layoutParams);

        $layout->getUpdate()->addHandle($params['handle']);
        $layout->getUpdate()->load();

        $layout->generateXml();
        $layout->generateElements();

        foreach ($layout->getAllBlocks() as $block) {
            /* @var $block Mage_Core_Block_Abstract */
            foreach ($params as $k => $v) {
                if (in_array($k, $skipParams)) {
                    continue;
                }

                $block->setDataUsingMethod($k, $v);
            }
        }

        /**
         * Add output method for first block
         */
        $allBlocks = $layout->getAllBlocks();
        $firstBlock = reset($allBlocks);
        if ($firstBlock) {
            $layout->addOutputElement($firstBlock->getNameInLayout());
        }

        $layout->setDirectOutput(false);
        $result = $layout->getOutput();
        $layout->__destruct(); // To overcome bug with SimpleXML memory leak (https://bugs.php.net/bug.php?id=62468)
        return $result;
    }

    /**
     * Retrieve block parameters
     *
     * @param mixed $value
     * @return array
     */
    protected function _getBlockParameters($value)
    {
        $tokenizer = new Varien_Filter_Template_Tokenizer_Parameter();
        $tokenizer->setString($value);

        return $tokenizer->tokenize();
    }

    /**
     * Retrieve View URL directive
     *
     * @param array $construction
     * @return string
     */
    public function viewDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $url = $this->_viewUrl->getViewFileUrl($params['url'], $params);
        return $url;
    }

    /**
     * Retrieve media file URL directive
     *
     * @param array $construction
     * @return string
     */
    public function mediaDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        return Mage::getBaseUrl('media') . $params['url'];
    }

    /**
     * Retrieve store URL directive
     * Support url and direct_url properties
     *
     * @param array $construction
     * @return string
     */
    public function storeDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (!isset($params['_query'])) {
            $params['_query'] = array();
        }
        foreach ($params as $k => $v) {
            if (strpos($k, '_query_') === 0) {
                $params['_query'][substr($k, 7)] = $v;
                unset($params[$k]);
            }
        }
        $params['_absolute'] = $this->_useAbsoluteLinks;

        if ($this->_useSessionInUrl === false) {
            $params['_nosid'] = true;
        }

        if (isset($params['direct_url'])) {
            $path = '';
            $params['_direct'] = $params['direct_url'];
            unset($params['direct_url']);
        } else {
            $path = isset($params['url']) ? $params['url'] : '';
            unset($params['url']);
        }

        return Mage::app()->getStore($this->getStoreId())->getUrl($path, $params);
    }

    /**
     * Directive for converting special characters to HTML entities
     * Supported options:
     *     allowed_tags - Comma separated html tags that have not to be converted
     *
     * @param array $construction
     * @return string
     */
    public function escapehtmlDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        if (!isset($params['var'])) {
            return '';
        }

        $allowedTags = null;
        if (isset($params['allowed_tags'])) {
            $allowedTags = preg_split('/\s*\,\s*/', $params['allowed_tags'], 0, PREG_SPLIT_NO_EMPTY);
        }

        return Mage::helper('Mage_Core_Helper_Data')->escapeHtml($params['var'], $allowedTags);
    }

    /**
     * Var directive with modifiers support
     *
     * @param array $construction
     * @return string
     */
    public function varDirective($construction)
    {
        if (count($this->_templateVars)==0) {
            // If template preprocessing
            return $construction[0];
        }

        $parts = explode('|', $construction[2], 2);
        if (2 === count($parts)) {
            list($variableName, $modifiersString) = $parts;
            return $this->_amplifyModifiers($this->_getVariable($variableName, ''), $modifiersString);
        }
        return $this->_getVariable($construction[2], '');
    }

    /**
     * Apply modifiers one by one, with specified params
     *
     * Modifier syntax: modifier1[:param1:param2:...][|modifier2:...]
     *
     * @param string $value
     * @param string $modifiers
     * @return string
     */
    protected function _amplifyModifiers($value, $modifiers)
    {
        foreach (explode('|', $modifiers) as $part) {
            if (empty($part)) {
                continue;
            }
            $params   = explode(':', $part);
            $modifier = array_shift($params);
            if (isset($this->_modifiers[$modifier])) {
                $callback = $this->_modifiers[$modifier];
                if (!$callback) {
                    $callback = $modifier;
                }
                array_unshift($params, $value);
                $value = call_user_func_array($callback, $params);
            }
        }
        return $value;
    }

    /**
     * Escape specified string
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    public function modifierEscape($value, $type = 'html')
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars($value, ENT_QUOTES);

            case 'htmlentities':
                return htmlentities($value, ENT_QUOTES);

            case 'url':
                return rawurlencode($value);
        }
        return $value;
    }

    /**
     * HTTP Protocol directive
     *
     * Using:
     * {{protocol}} - current protocol http or https
     * {{protocol url="www.domain.com/"}} domain URL with current protocol
     * {{protocol http="http://url" https="https://url"}
     * also allow additional parameter "store"
     *
     * @param array $construction
     * @return string
     */
    public function protocolDirective($construction)
    {
        $params = $this->_getIncludeParameters($construction[2]);
        $store = null;
        if (isset($params['store'])) {
            $store = Mage::app()->getSafeStore($params['store']);
        }
        $isSecure = Mage::app()->getStore($store)->isCurrentlySecure();
        $protocol = $isSecure ? 'https' : 'http';
        if (isset($params['url'])) {
            return $protocol . '://' . $params['url'];
        } elseif (isset($params['http']) && isset($params['https'])) {
            if ($isSecure) {
                return $params['https'];
            }
            return $params['http'];
        }

        return $protocol;
    }

    /**
     * Store config directive
     *
     * @param array $construction
     * @return string
     */
    public function configDirective($construction)
    {
        $configValue = '';
        $params = $this->_getIncludeParameters($construction[2]);
        $storeId = $this->getStoreId();
        if (isset($params['path'])) {
            $configValue = Mage::getStoreConfig($params['path'], $storeId);
        }
        return $configValue;
    }

    /**
     * Custom Variable directive
     *
     * @param array $construction
     * @return string
     */
    public function customvarDirective($construction)
    {
        $customVarValue = '';
        $params = $this->_getIncludeParameters($construction[2]);
        if (isset($params['code'])) {
            $variable = Mage::getModel('Mage_Core_Model_Variable')
                ->setStoreId($this->getStoreId())
                ->loadByCode($params['code']);
            $mode = $this->getPlainTemplateMode()
                ? Mage_Core_Model_Variable::TYPE_TEXT
                : Mage_Core_Model_Variable::TYPE_HTML;
            $value = $variable->getValue($mode);
            if ($value) {
                $customVarValue = $value;
            }
        }
        return $customVarValue;
    }

    /**
     * Filter the string as template.
     * Rewrited for logging exceptions
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        try {
            $value = parent::filter($value);
        } catch (Exception $e) {
            $value = '';
            Mage::logException($e);
        }
        return $value;
    }
}
