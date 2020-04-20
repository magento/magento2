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
 * Plugin for putting all JavaScript tags to the end of body.
 */
class JsFooterPlugin
{
    private const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

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
     * Moves all JavaScript tags to the end of body if this feature is enabled.
     *
     * @param Http $subject
     * @return void
     */
    public function beforeSendResponse(Http $subject)
    {
        $content = (string)$subject->getContent();
        $bodyEndTag = '</body';
        $isEndBodyTagFound = strpos($content, $bodyEndTag) !== false;
        $shouldMoveJsToBottom = $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
            ScopeInterface::SCOPE_STORE
        );

        if ($isEndBodyTagFound && $shouldMoveJsToBottom) {
            $scripts = $this->extractScriptTags($content);
            if ($scripts) {
                $content = str_replace($bodyEndTag, "$scripts\n$bodyEndTag", $content);
                $subject->setContent($content);
            }
        }
    }

    /**
     * Extracts and returns script tags found in given content.
     *
     * @param string $content
     */
    private function extractScriptTags(&$content): string
    {
        $scripts = '';
        $scriptOpen = '<script';
        $scriptClose = '</script>';
        $scriptOpenPos = strpos($content, $scriptOpen);

        while ($scriptOpenPos !== false) {
            $scriptClosePos = strpos($content, $scriptClose, $scriptOpenPos);
            $script = substr($content, $scriptOpenPos, $scriptClosePos - $scriptOpenPos + strlen($scriptClose));
            $isXMagentoTemplate = strpos($script, 'text/x-magento-template') !== false;

            if ($isXMagentoTemplate) {
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                continue;
            }

            $scripts .= "\n" . $script;
            $content = str_replace($script, '', $content);
            // Script cut out, continue search from its position.
            $scriptOpenPos = strpos($content, $scriptOpen, $scriptOpenPos);
        }

        return $scripts;
    }
}
