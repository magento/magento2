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
        $content = (string)$subject->getContent();

        $headClose = '</head>';

        if (strpos($content, $headClose) !== false && $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_CSS_CRITICAL_PATH,
            ScopeInterface::SCOPE_STORE
        )) {
            $styles = '';
            $styleOpen = '<link';
            $styleClose = '>';
            $styleOpenPos = strpos($content, $styleOpen);

            while ($styleOpenPos !== false) {
                $styleClosePos = strpos($content, $styleClose, $styleOpenPos);
                $style = substr($content, $styleOpenPos, $styleClosePos - $styleOpenPos + strlen($styleClose));

                if (!preg_match('@rel=["\']stylesheet["\']@', $style)) {
                    // Link is not a stylesheet, search for another one after it.
                    $styleOpenPos = strpos($content, $styleOpen, $styleClosePos);
                    continue;
                }
                // Remove the link from HTML to add it before </head> tag later.
                $content = str_replace($style, '', $content);

                preg_match('@href=("|\')(.*?)\1@', $style, $hrefAttribute);
                $href = $hrefAttribute[2];

                if (preg_match('@media=("|\')(.*?)\1@', $style, $mediaAttribute)) {
                    $media = $mediaAttribute[2];
                }
                $media = $media ?? 'all';

                $style = sprintf(
                    '<link rel="stylesheet" media="print"' .
                    ' onload="this.onload=null;this.media=\'%s\'"' .
                    ' href="%s">',
                    $media,
                    $href
                );
                $styles .= "\n" . $style;
                // Link was cut out, search for the next one at its former position.
                $styleOpenPos = strpos($content, $styleOpen, $styleOpenPos);
            }

            if ($styles) {
                $content = str_replace($headClose, $styles . "\n" . $headClose, $content);
                $subject->setContent($content);
            }
        }
    }
}
