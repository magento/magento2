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
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Core\Model\Translate\Inline\ConfigFactory
     */
    protected $_configFactory;

    /**
     * Initialize inline translation model
     *
     * @param InlineParser $parser
     * @param \Magento\Core\Model\Translate $translate
     * @param \Magento\Core\Model\Url $url
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Core\Model\Translate\Inline\ConfigFactory $configFactory
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Core\Model\Translate\InlineParser $parser,
        \Magento\Core\Model\Translate $translate,
        \Magento\Core\Model\Url $url,
        \Magento\View\LayoutInterface $layout,
        \Magento\Core\Model\Translate\Inline\ConfigFactory $configFactory,
        \Magento\App\State $appState
    ) {
        $this->_configFactory = $configFactory;
        $this->_parser = $parser;
        $this->_translator = $translate;
        $this->_url = $url;
        $this->_layout = $layout;
        $this->_appState = $appState;
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

            $active = $this->_configFactory->create()->isActive($store);
            $this->_isAllowed = $active && $this->_parser->getHelper()->isDevAllowed($store);
        }
        return $this->_translator->getTranslateInline() && $this->_isAllowed;
    }

    /**
     * Disable inline translation functionality
     */
    public function disable()
    {
        $this->_isAllowed = false;
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
            if ($this->_appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
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

        /** @var $block \Magento\View\Element\Template */
        $block = $this->_layout->createBlock('Magento\View\Element\Template');

        $block->setAjaxUrl($this->_getAjaxUrl());

        $block->setTemplate('Magento_Core::translate_inline.phtml');

        $html = $block->toHtml();

        $this->_parser->setContent(str_ireplace('</body>', $html . '</body>', $content));

        $this->_isScriptInserted = true;
    }

    /**
     * Return URL for ajax requests
     *
     * @return string
     */
    protected function _getAjaxUrl()
    {
        $store = $this->_parser->getStoreManager()->getStore();
        return $this->_url->getUrl('core/ajax/translate', array('_secure' => $store->isCurrentlySecure()));
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
