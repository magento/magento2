<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Translate;

/**
 * Inline translation specific to Vde.
 */
class Inline implements \Magento\Framework\Translate\InlineInterface
{
    /**
     * data-translate-mode attribute name
     */
    const TRANSLATE_MODE = 'data-translate-mode';

    /**#@+
     * Translate modes
     */
    const MODE_TEXT = 'text';
    const MODE_ALT = 'alt';
    const MODE_SCRIPT = 'script';
    /**#@-*/

    /**#@+
     * Html tags
     */
    const TAG_IMG = 'img';
    const TAG_SCRIPT = 'script';
    /**#@-*/

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Translate\Inline\ParserInterface
     */
    protected $_parser;

    /**
     * @var \Magento\Framework\Translate\Inline\ParserFactory
     */
    protected $parserFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $_isScriptInserted = false;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * Initialize inline translation model specific for vde
     *
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Translate\Inline\ParserFactory $parserFactory
     * @param \Magento\DesignEditor\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Translate\Inline\ParserFactory $parserFactory,
        \Magento\DesignEditor\Helper\Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_design = $design;
        $this->_scopeResolver = $scopeResolver;
        $this->parserFactory = $parserFactory;
        $this->_helper = $helper;
        $this->_url = $url;
        $this->_objectManager = $objectManager;
    }

    /**
     * Check if Inline Translates is allowed
     *
     * Translation within the vde will be enabled by the client when the 'Edit' button is enabled.
     *
     * @return bool
     */
    public function isAllowed()
    {
        return $this->_helper->isAllowed();
    }

    /**
     * Retrieve Inline Parser instance
     *
     * @return \Magento\Framework\Translate\Inline\ParserInterface
     */
    public function getParser()
    {
        if (!$this->_parser) {
            $this->_parser = $this->parserFactory->create(['translateInline' => $this]);
        }
        return $this->_parser;
    }

    /**
     * Replace VDE specific translation templates with HTML fragments
     *
     * @param string[]|string &$body
     * @param bool $isJson
     * @return $this
     */
    public function processResponseBody(&$body, $isJson = false)
    {
        if (!$this->isAllowed()) {
            return $this;
        }

        $this->getParser()->setIsJson($isJson);

        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part, $isJson);
            }
        } elseif (is_string($body)) {
            $this->getParser()->processResponseBodyString($body);
            $this->addInlineScript();
            $body = $this->getParser()->getContent();
        }

        $this->getParser()->setIsJson(false);

        return $this;
    }

    /**
     * Returns the translation mode html attribute needed by vde to specify which translation mode the
     * element represents.
     *
     * @param string|null $tagName
     * @return string
     */
    public function getAdditionalHtmlAttribute($tagName = null)
    {
        return self::TRANSLATE_MODE . '="' . $this->_getTranslateMode($tagName) . '"';
    }

    /**
     * Add inline script code
     *
     * Insert script and html with
     * added inline translation content specific for vde.
     *
     * @return void
     */
    protected function addInlineScript()
    {
        $content = $this->getParser()->getContent();
        if (stripos($content, '</body>') === false) {
            return;
        }
        if (!$this->_isScriptInserted) {
            $this->getParser()->setContent(str_ireplace('</body>', $this->getInlineScript() . '</body>', $content));
            $this->_isScriptInserted = true;
        }
    }

    /**
     * Retrieve inline script code
     *
     * Create block to render script and html with
     * added inline translation content specific for vde.
     *
     * @return string
     */
    protected function getInlineScript()
    {
        /** @var $block \Magento\Framework\View\Element\Template */
        $block = $this->_objectManager->create('Magento\Framework\View\Element\Template');

        $block->setArea($this->_design->getArea());
        $block->setAjaxUrl($this->_getAjaxUrl());
        $block->setFrameUrl($this->_getFrameUrl());
        $block->setRefreshCanvas($this->isAllowed());

        $block->setTemplate('Magento_DesignEditor::translate_inline.phtml');
        $block->setTranslateMode($this->_helper->getTranslationMode());

        return $block->toHtml();
    }

    /**
     * Return URL for ajax requests
     *
     * @return string
     */
    protected function _getAjaxUrl()
    {
        return $this->_url->getUrl(
            'translation/ajax/index',
            [
                '_secure' => $this->_scopeResolver->getScope()->isCurrentlySecure(),
                \Magento\DesignEditor\Helper\Data::TRANSLATION_MODE => $this->_helper->getTranslationMode()
            ]
        );
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
     * @param string $tagName
     * @return string
     */
    private function _getTranslateMode($tagName)
    {
        switch ($tagName) {
            case self::TAG_SCRIPT:
                $result = self::MODE_SCRIPT;
                break;
            case self::TAG_IMG:
                $result = self::MODE_ALT;
                break;
            default:
                $result = self::MODE_TEXT;
        }
        return $result;
    }
}
