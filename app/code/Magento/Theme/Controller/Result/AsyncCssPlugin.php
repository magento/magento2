<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

        if (\is_string($content) && strpos($content, '</body') !== false && $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_CSS_CRITICAL_PATH,
            ScopeInterface::SCOPE_STORE
        )) {
            $cssMatches = [];
            // add link rel preload to style sheets
            $content = preg_replace_callback(
                '@<link\b.*?rel=("|\')stylesheet\1.*?/>@',
                function ($matches) use (&$cssMatches) {
                    $cssMatches[] = $matches[0];
                    preg_match('@href=("|\')(.*?)\1@', $matches[0], $hrefAttribute);
                    $href = $hrefAttribute[2];
                    if (preg_match('@media=("|\')(.*?)\1@', $matches[0], $mediaAttribute)) {
                        $media = $mediaAttribute[2];
                    }
                    $media = $media ?? 'all';
                    $loadCssAsync = sprintf(
                        '<link rel="preload" as="style" media="%s" .
                         onload="this.onload=null;this.rel=\'stylesheet\'"' .
                        'href="%s">',
                        $media,
                        $href
                    );

                    return $loadCssAsync;
                },
                $content
            );

            if (!empty($cssMatches)) {
                $content = str_replace('</body', implode("\n", $cssMatches) . "\n</body", $content);
                $subject->setContent($content);
            }
        }
    }
}
