<?php
/**
 * Inline Translations Library
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate;

class Inline implements \Magento\Framework\Translate\InlineInterface
{
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $isAllowed;

    /**
     * @var \Magento\Framework\Translate\Inline\ParserInterface
     */
    protected $parser;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $isScriptInserted = false;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Translate\Inline\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
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
     * Initialize inline translation model
     *
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param Inline\ConfigInterface $config
     * @param Inline\ParserInterface $parser
     * @param Inline\StateInterface $state
     * @param string $templateFileName
     * @param string $translatorRoute
     * @param null $scope
     */
    public function __construct(
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Translate\Inline\ConfigInterface $config,
        \Magento\Framework\Translate\Inline\ParserInterface $parser,
        \Magento\Framework\Translate\Inline\StateInterface $state,
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
            if (!$this->scope instanceof \Magento\Framework\App\ScopeInterface) {
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
        if (!$this->isAllowed()) {
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        /** @var $block \Magento\Framework\View\Element\Template */
        $block = $this->layout->createBlock(\Magento\Framework\View\Element\Template::class);

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
        } else {
            if (is_string($body)) {
                $body = preg_replace(
                    '#' . \Magento\Framework\Translate\Inline\ParserInterface::REGEXP_TOKEN . '#',
                    '$1',
                    $body
                );
            }
        }
        return $this;
    }
}
