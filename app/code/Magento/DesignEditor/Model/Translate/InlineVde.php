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
 * Inline translation specific to Vde.
 */
namespace Magento\DesignEditor\Model\Translate;

class InlineVde implements \Magento\Core\Model\Translate\InlineInterface
{
    /**
     * data-translate-mode attribute name
     */
    const TRANSLATE_MODE = 'data-translate-mode';

    /**
     * text translate mode
     */
    const MODE_TEXT = 'text';

    /**
     * img element name
     */
    const ELEMENT_IMG = 'img';

    /**
     * alt translate mode
     */
    const MODE_ALT = 'alt';

    /**
     * script translate mode
     */
    const MODE_SCRIPT = 'script';

    /**
     * script element name
     */
    const ELEMENT_SCRIPT = self::MODE_SCRIPT;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Core\Model\Translate\InlineParser
     */
    protected $_parser;

    /**
     * @var \Magento\Core\Model\Url
     */
    protected $_url;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $_isScriptInserted = false;

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Initialize inline translation model specific for vde
     *
     * @param \Magento\Core\Model\Translate\InlineParser $parser
     * @param \Magento\DesignEditor\Helper\Data $helper
     * @param \Magento\Core\Model\Url $url
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Core\Model\Translate\InlineParser $parser,
        \Magento\DesignEditor\Helper\Data $helper,
        \Magento\Core\Model\Url $url,
        \Magento\ObjectManager $objectManager
    ) {
        $this->_parser = $parser;
        $this->_helper = $helper;
        $this->_url = $url;
        $this->_objectManager = $objectManager;
    }

    /**
     * Translation within the vde will be enabled by the client when the 'Edit' button is enabled.
     *
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_helper->isAllowed();
    }

    /**
     * Replace VDE specific translation templates with HTML fragments
     *
     * @param array|string $body
     * @param bool $isJson
     * @return \Magento\DesignEditor\Model\Translate\InlineVde
     */
    public function processResponseBody(&$body, $isJson)
    {
        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part, $isJson);
            }
        } elseif (is_string($body)) {
            $content = $this->_parser->processResponseBodyString($body, $this);
            $this->_insertInlineScriptsHtml($content);
            $body = $this->_parser->getContent();
        }
        return $this;
    }

    /**
     * Returns the translation mode html attribute needed by vde to specify which translation mode the
     * element represents.
     *
     * @param mixed|string $tagName
     * @return string
     */
    public function getAdditionalHtmlAttribute($tagName = null)
    {
        return self::TRANSLATE_MODE . '="' . $this->_getTranslateMode($tagName) . '"';
    }

    /**
     * Create block to render script and html with added inline translation content specific for vde.
     */
    private function _insertInlineScriptsHtml($content)
    {
        if ($this->_isScriptInserted || stripos($content, '</body>') === false) {
            return;
        }

        $store = $this->_parser->getStoreManager()->getStore();
        $ajaxUrl = $this->_url->getUrl('core/ajax/translate', array(
            '_secure' => $store->isCurrentlySecure(),
            \Magento\DesignEditor\Helper\Data::TRANSLATION_MODE => $this->_helper->getTranslationMode()
        ));

        /** @var $block \Magento\Core\Block\Template */
        $block = $this->_objectManager->create('Magento\Core\Block\Template');

        $block->setArea($this->_parser->getDesignPackage()->getArea());
        $block->setAjaxUrl($ajaxUrl);

        $block->setFrameUrl($this->_getFrameUrl());
        $block->setRefreshCanvas($this->isAllowed());

        $block->setTemplate('Magento_DesignEditor::translate_inline.phtml');
        $block->setTranslateMode($this->_helper->getTranslationMode());

        $this->_parser->setContent(str_ireplace('</body>', $block->toHtml() . '</body>', $content));

        $this->_isScriptInserted = true;
    }

    /**
     * Generate frame url
     *
     * @return string
     */
    protected function _getFrameUrl()
    {
        /** @var \Magento\Backend\Model\Session $backendSession */
        $backendSession = $this->_objectManager->get('Magento\Backend\Model\Session');

        /** @var $vdeUrlModel \Magento\DesignEditor\Model\Url\NavigationMode */
        $vdeUrlModel = $this->_objectManager->create('Magento\DesignEditor\Model\Url\NavigationMode');
        $currentUrl = $backendSession->getData(\Magento\DesignEditor\Model\State::CURRENT_URL_SESSION_KEY);

        return $vdeUrlModel->getUrl(ltrim($currentUrl, '/'));
    }

    /**
     * Get inline vde translate mode
     *
     * @param string  $tagName
     * @return string
     */
    private function _getTranslateMode($tagName)
    {
        $mode = self::MODE_TEXT;
        if (self::ELEMENT_SCRIPT == $tagName) {
            $mode = self::MODE_SCRIPT;
        } elseif (self::ELEMENT_IMG == $tagName) {
            $mode = self::MODE_ALT;
        }
        return $mode;
    }
}
