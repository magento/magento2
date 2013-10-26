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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Inline Translations PHP part
 */
namespace Magento\Core\Model\Translate;

class Inline implements \Magento\Core\Model\Translate\InlineInterface
{
    /**
     * Regular Expression for detected and replace translate
     *
     * @var string
     */
    protected $_tokenRegex = '\{\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\}';

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translator;
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $_isAllowed;

    /**
     * @var \Magento\Core\Model\Translate\InlineParser
     */
    protected $_parser;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $_isScriptInserted    = false;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * Initialize inline translation model
     *
     * @param InlineParser $parser
     * @param \Magento\Core\Model\Translate $translate
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Core\Model\Url $url
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     */
    public function __construct(
        \Magento\Core\Model\Translate\InlineParser $parser,
        \Magento\Core\Model\Translate $translate,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Core\Model\Url $url,
        \Magento\View\LayoutInterface $layout,
        \Magento\Core\Model\Store\Config $coreStoreConfig
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_parser = $parser;
        $this->_translator = $translate;
        $this->_backendUrl = $backendUrl;
        $this->_url = $url;
        $this->_layout = $layout;
    }

    /**
     * Is enabled and allowed Inline Translates
     *
     * @param mixed $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        if (is_null($this->_isAllowed)) {
            if (is_null($store)) {
                $store = $this->_parser->getStoreManager()->getStore();
            }
            if (!$store instanceof \Magento\Core\Model\Store) {
                $store = $this->_parser->getStoreManager()->getStore($store);
            }

            if ($this->_parser->getDesignPackage()->getArea() == 'adminhtml') {
                $active = $this->_coreStoreConfig->getConfigFlag('dev/translate_inline/active_admin', $store);
            } else {
                $active = $this->_coreStoreConfig->getConfigFlag('dev/translate_inline/active', $store);
            }
            $this->_isAllowed = $active && $this->_parser->getHelper()->isDevAllowed($store);
        }
        return $this->_translator->getTranslateInline() && $this->_isAllowed;
    }

    /**
     * Replace translation templates with HTML fragments
     *
     * @param array|string $body
     * @param bool $isJson
     * @return \Magento\Core\Model\Translate\Inline
     */
    public function processResponseBody(&$body, $isJson)
    {
        $this->_parser->setIsJson($isJson);
        if (!$this->isAllowed()) {
            if ($this->_parser->getDesignPackage()->getArea() == \Magento\Backend\Helper\Data::BACKEND_AREA_CODE) {
                $this->_stripInlineTranslations($body);
            }
            return $this;
        }

        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part, $isJson);
            }
        } elseif (is_string($body)) {
            $content = $this->_parser->processResponseBodyString($body, $this);
            $this->_insertInlineScriptsHtml($content);
            $body = $this->_parser->getContent();
        }
        $this->_parser->setIsJson(\Magento\Core\Model\Translate\InlineParser::JSON_FLAG_DEFAULT_STATE);
        return $this;
    }

    /**
     * Additional translation mode html attribute is not needed for base inline translation.
     *
     * @param mixed|string $tagName
     * @return string
     */
    public function getAdditionalHtmlAttribute($tagName = null)
    {
        return null;
    }

    /**
     * Create block to render script and html with added inline translation content.
     */
    private function _insertInlineScriptsHtml($content)
    {
        if ($this->_isScriptInserted || stripos($content, '</body>') === false) {
            return;
        }

        $store = $this->_parser->getStoreManager()->getStore();
        if ($store->isAdmin()) {
            $urlPrefix = \Magento\Backend\Helper\Data::BACKEND_AREA_CODE;
            $urlModel = $this->_backendUrl;
        } else {
            $urlPrefix = 'core';
            $urlModel = $this->_url;
        }
        $ajaxUrl = $urlModel->getUrl($urlPrefix . '/ajax/translate',
            array('_secure' => $store->isCurrentlySecure()));

        /** @var $block \Magento\Core\Block\Template */
        $block = $this->_layout->createBlock('Magento\Core\Block\Template');

        $block->setAjaxUrl($ajaxUrl);

        $block->setTemplate('Magento_Core::translate_inline.phtml');

        $html = $block->toHtml();

        $this->_parser->setContent(str_ireplace('</body>', $html . '</body>', $content));

        $this->_isScriptInserted = true;
    }

    /**
     * Strip inline translations from text
     *
     * @param array|string $body
     * @return \Magento\Core\Model\Translate\Inline
     */
    private function _stripInlineTranslations(&$body)
    {
        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->_stripInlineTranslations($part);
            }
        } else if (is_string($body)) {
            $body = preg_replace('#' . $this->_tokenRegex . '#', '$1', $body);
        }
        return $this;
    }
}
