<?php
/**
 * Inline Translations Library
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Translate\Inline\ConfigInterface;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

/**
 * Translate Inline Class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Inline implements InlineInterface
{
    /**
     * Indicator to hold state of whether inline translation is allowed
     *
     * @var bool
     */
    protected $isAllowed;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $isScriptInserted = false;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ScopeResolverInterface
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
     * @var array
     */
    private $allowedAreas = [Area::AREA_FRONTEND, Area::AREA_ADMINHTML];

    /**
     * @var State
     */
    private $appState;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param UrlInterface $url
     * @param LayoutInterface $layout
     * @param Inline\ConfigInterface $config
     * @param Inline\ParserInterface $parser
     * @param Inline\StateInterface $state
     * @param string $templateFileName
     * @param string $translatorRoute
     * @param string|null $scope
     * @param State|null $appState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        UrlInterface $url,
        LayoutInterface $layout,
        ConfigInterface $config,
        ParserInterface $parser,
        StateInterface $state,
        $templateFileName = '',
        $translatorRoute = '',
        $scope = null,
        ?State $appState = null
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
        $this->appState = $appState ?: ObjectManager::getInstance()->get(State::class);
    }

    /**
     * Check if Inline Translates is allowed
     *
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->isAllowed === null) {
            $scope = $this->scope instanceof ScopeInterface ? null : $this->scopeResolver->getScope($this->scope);

            $this->isAllowed = $this->config->isActive($scope)
                && $this->config->isDevAllowed($scope)
                && $this->isAreaAllowed();
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
     * @param array|string $body
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
            $this->getParser()->setContent(
                str_ireplace('</body>', $this->getInlineScript() . '</body>', $content)
            );
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
        /** @var $block Template */
        $block = $this->layout->createBlock(Template::class);

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
        } elseif (is_string($body)) {
            $body = preg_replace('#' . ParserInterface::REGEXP_TOKEN . '#', '$1', $body);
        }

        return $this;
    }

    /**
     * Indicates whether the current area is valid for inline translation
     *
     * @return bool
     */
    private function isAreaAllowed(): bool
    {
        try {
            return in_array($this->appState->getAreaCode(), $this->allowedAreas, true);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
