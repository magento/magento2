<?php
/**
 * Inline Translations Library
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Translate;

class Inline implements \Magento\Translate\InlineInterface
{
    /**
     * Regular Expression for detected and replace translate
     *
     * @var string
     */
    protected $_tokenRegex = '\{\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\}';

    /**
     * @var \Magento\TranslateInterface
     */
    protected $_translator;
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $_isAllowed;

    /**
     * @var \Magento\Translate\Inline\ParserFactory
     */
    protected $_parserFactory;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $_isScriptInserted    = false;

    /**
     * @var \Magento\UrlInterface
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
     * @var \Magento\Translate\Inline\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @var \Magento\BaseScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var string
     */
    protected $_templateFileName = '';

    /**
     * @var string
     */
    protected $_translatorRoute = '';

    /**
     * Initialize inline translation model
     *
     * @param \Magento\BaseScopeResolverInterface $scopeResolver
     * @param \Magento\Translate\Inline\ParserFactory $parserFactory
     * @param \Magento\TranslateInterface $translate
     * @param \Magento\UrlInterface $url
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Translate\Inline\ConfigFactory $configFactory
     * @param \Magento\App\State $appState
     * @param string $templateFileName
     * @param string $translatorRoute
     */
    public function __construct(
        \Magento\BaseScopeResolverInterface $scopeResolver,
        \Magento\Translate\Inline\ParserFactory $parserFactory,
        \Magento\TranslateInterface $translate,
        \Magento\UrlInterface $url,
        \Magento\View\LayoutInterface $layout,
        \Magento\Translate\Inline\ConfigFactory $configFactory,
        \Magento\App\State $appState,
        $templateFileName = '',
        $translatorRoute = ''
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_configFactory = $configFactory;
        $this->_parserFactory = $parserFactory;
        $this->_translator = $translate;
        $this->_url = $url;
        $this->_layout = $layout;
        $this->_appState = $appState;
        $this->_templateFileName = $templateFileName;
        $this->_translatorRoute = $translatorRoute;
    }

    /**
     * Is enabled and allowed Inline Translates
     *
     * @param mixed $scope
     * @return bool
     */
    public function isAllowed($scope = null)
    {
        if (is_null($this->_isAllowed)) {
            if (!$scope instanceof \Magento\BaseScopeInterface) {
                $scope = $this->_scopeResolver->getScope($scope);
            }

            $config = $this->_configFactory->get();
            $this->_isAllowed = $config->isActive($scope) && $config->isDevAllowed($scope);
        }
        return $this->_translator->getTranslateInline() && $this->_isAllowed;
    }

    /**
     * Disable inline translation functionality
     *
     * @return void
     */
    public function disable()
    {
        $this->_isAllowed = false;
    }

    /**
     * Replace translation templates with HTML fragments
     *
     * @param array|string &$body
     * @param bool $isJson
     * @return $this
     */
    public function processResponseBody(&$body, $isJson = false)
    {
        $parser = $this->_parserFactory->get();
        $parser->setIsJson($isJson);
        if (!$this->isAllowed()) {
            return $this;
        }

        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part, $isJson);
            }
        } elseif (is_string($body)) {
            $content = $parser->processResponseBodyString($body, $this);
            $this->_insertInlineScriptsHtml($content);
            $body = $parser->getContent();
        }
        $parser->setIsJson(\Magento\Translate\Inline\ParserInterface::JSON_FLAG_DEFAULT_STATE);
        return $this;
    }

    /**
     * Additional translation mode html attribute is not needed for base inline translation.
     *
     * @param mixed|string|null $tagName
     * @return null
     */
    public function getAdditionalHtmlAttribute($tagName = null)
    {
        return null;
    }

    /**
     * Create block to render script and html with added inline translation content.
     *
     * @param string $content
     * @return void
     */
    protected function _insertInlineScriptsHtml($content)
    {
        if ($this->_isScriptInserted || stripos($content, '</body>') === false) {
            return;
        }

        /** @var $block \Magento\View\Element\Template */
        $block = $this->_layout->createBlock('Magento\View\Element\Template');

        $block->setAjaxUrl($this->_getAjaxUrl());

        $block->setTemplate($this->_templateFileName);

        $html = $block->toHtml();

        $this->_parserFactory->get()->setContent(str_ireplace('</body>', $html . '</body>', $content));

        $this->_isScriptInserted = true;
    }

    /**
     * Return URL for ajax requests
     *
     * @return string
     */
    protected function _getAjaxUrl()
    {
        $scope = $this->_scopeResolver->getScope();
        return $this->_url->getUrl($this->_translatorRoute, array('_secure' => $scope->isCurrentlySecure()));
    }

    /**
     * Strip inline translations from text
     *
     * @param array|string &$body
     * @return $this
     */
    protected function _stripInlineTranslations(&$body)
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
