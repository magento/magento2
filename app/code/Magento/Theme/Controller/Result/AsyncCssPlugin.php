<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Result;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Response\Http;

/**
 * Plugin for asynchronous CSS loading.
 */
class AsyncCssPlugin
{
    private const XML_PATH_USE_CSS_CRITICAL_PATH = 'dev/css/use_css_critical_path';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Load CSS asynchronously if it is enabled in configuration.
     *
     * @param Http $subject
     * @return void
     */
    public function beforeSendResponse(Http $subject): void
    {
        $content = $subject->getContent();
        // loadCSS rel=preload polyfill script https://github.com/filamentgroup/loadCSS/blob/v2.0.1/src/cssrelpreload.js
        $loadCssScript = '!function(t){"use strict";t.loadCSS||(t.loadCSS=function(){});var e=loadCSS.relpreload={};if(e.support=function(){var e;try{e=t.document.createElement("link").relList.supports("preload")}catch(t){e=!1}return function(){return e}}(),e.bindMediaToggle=function(t){var e=t.media||"all";function a(){t.media=e}t.addEventListener?t.addEventListener("load",a):t.attachEvent&&t.attachEvent("onload",a),setTimeout(function(){t.rel="stylesheet",t.media="only x"}),setTimeout(a,3e3)},e.poly=function(){if(!e.support())for(var a=t.document.getElementsByTagName("link"),n=0;n<a.length;n++){var o=a[n];"preload"!==o.rel||"style"!==o.getAttribute("as")||o.getAttribute("data-loadcss")||(o.setAttribute("data-loadcss",!0),e.bindMediaToggle(o))}},!e.support()){e.poly();var a=t.setInterval(e.poly,500);t.addEventListener?t.addEventListener("load",function(){e.poly(),t.clearInterval(a)}):t.attachEvent&&t.attachEvent("onload",function(){e.poly(),t.clearInterval(a)})}"undefined"!=typeof exports?exports.loadCSS=loadCSS:t.loadCSS=loadCSS}("undefined"!=typeof global?global:this);';

        if (strpos($content, '</body') !== false && $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_CSS_CRITICAL_PATH,
            ScopeInterface::SCOPE_STORE
            )) {
            // add link rel preload to style sheets
            $content = preg_replace_callback(
                '@<link\b.*?rel=("|\')stylesheet\1.*?/>@',
                function ($matches) {
                    preg_match('@href=("|\')(.*?)\1@', $matches[0], $hrefAttribute);
                    $href = $hrefAttribute[2];
                    if (preg_match('@media=("|\')(.*?)\1@', $matches[0], $mediaAttribute)) {
                        $media = $mediaAttribute[2];
                    }
                    $media = $media ?? 'all';
                    $loadCssAsync = sprintf(
                        '<link rel="preload" as="style" media="%s" onload="this.onload=null;this.rel=\'stylesheet\'"' .
                        'href="%s"><noscript><link rel="stylesheet" href="%s"></noscript>',
                        $media,
                        $href,
                        $href
                    );

                    return $loadCssAsync;
                },
                $content
            );
            // add CSS rel preload polyfill script
            $pattern = '@</head>@';
            $replacement = '<script>' . $loadCssScript . '</script></head>';
            $content = preg_replace($pattern, $replacement, $content);

            $subject->setContent($content);
        }
    }
}
