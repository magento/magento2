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

use Magento\BaseScopeInterface;

class Inline implements \Magento\Translate\InlineInterface
{
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $isAllowed;

    /**
     * @var \Magento\Translate\Inline\ParserInterface
     */
    protected $parser;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $isScriptInserted = false;

    /**
     * @var \Magento\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Translate\Inline\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\BaseScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var string
     */
    protected $templateFileName;

    /**
     * @var string
     */
    protected $translatorRoute;

    /**
     * @var null|string
     */
    protected $scope;

    /**
     * @var Inline\StateInterface
     */
    protected $state;

    /**
     * @param \Magento\BaseScopeResolverInterface $scopeResolver
     * @param \Magento\UrlInterface $url
     * @param \Magento\View\LayoutInterface $layout
     * @param Inline\ConfigInterface $config
     * @param Inline\ParserInterface $parser
     * @param Inline\StateInterface $state
     * @param string $templateFileName
     * @param string $translatorRoute
     * @param null $scope
     */
    public function __construct(
        \Magento\BaseScopeResolverInterface $scopeResolver,
        \Magento\UrlInterface $url,
        \Magento\View\LayoutInterface $layout,
        \Magento\Translate\Inline\ConfigInterface $config,
        \Magento\Translate\Inline\ParserInterface $parser,
        \Magento\Translate\Inline\StateInterface $state,
        $templateFileName = '',
        $translatorRoute = '',
        $scope = null
    ) {
        $this->scopeResolver = $scopeResolver;
        $this->url = $url;
        $this->layout = $layout;
        $this->config = $config;
        $this->parser = $parser;
        $this->state = $state;
        $this->templateFileName = $templateFileName;
        $this->translatorRoute = $translatorRoute;
        $this->scope = $scope;
    }

    /**
     * Check if Inline Translates is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->isAllowed === null) {
            if (!$this->scope instanceof BaseScopeInterface) {
                $scope = $this->scopeResolver->getScope($this->scope);
            }
            $this->isAllowed = $this->config->isActive($scope)
                && $this->config->isDevAllowed($scope);
        }
        return $this->state->isEnabled() && $this->isAllowed;
    }

    /**
     * Retrieve Inline Parser instance
     *
     * @return Inline\ParserInterface
     */
    public function getParser()
    {
        return $this->parser;
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
        if ($this->scope == 'admin' && !$this->isAllowed()) {
            $this->stripInlineTranslations($body);
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
     * Add inline script code
     *
     * Insert script and html with
     * added inline translation content.
     *
     * @return void
     */
    protected function addInlineScript()
    {
        $content = $this->getParser()->getContent();
        if (stripos($content, '</body>') === false) {
            return;
        }
        if (!$this->isScriptInserted) {
            $this->getParser()->setContent(str_ireplace('</body>', $this->getInlineScript() . '</body>', $content));
            $this->isScriptInserted = true;
        }
    }

    /**
     * Retrieve inline script code
     *
     * Create block to render script and html with
     * added inline translation content.
     *
     * @return string
     */
    protected function getInlineScript()
    {
        /** @var $block \Magento\View\Element\Template */
        $block = $this->layout->createBlock('Magento\View\Element\Template');

        $block->setAjaxUrl($this->getAjaxUrl());
        $block->setTemplate($this->templateFileName);

        return $block->toHtml();
    }

    /**
     * Return URL for ajax requests
     *
     * @return string
     */
    protected function getAjaxUrl()
    {
        return $this->url->getUrl(
            $this->translatorRoute,
            ['_secure' => $this->scopeResolver->getScope()->isCurrentlySecure()]
        );
    }

    /**
     * Strip inline translations from text
     *
     * @param array|string &$body
     * @return $this
     */
    protected function stripInlineTranslations(&$body)
    {
        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->stripInlineTranslations($part);
            }
        } else if (is_string($body)) {
            $body = preg_replace('#' . \Magento\Translate\Inline\ParserInterface::REGEXP_TOKEN . '#', '$1', $body);
        }
        return $this;
    }
}
