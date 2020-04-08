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
 * Plugin for putting all js to footer.
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
     * Put all javascript to footer before sending the response.
     *
     * @param Http $subject
     * @return void
     */
    public function beforeSendResponse(Http $subject)
    {
        $content = (string)$subject->getContent();

        $bodyClose = '</body';

        if (strpos($content, $bodyClose) !== false && $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
            ScopeInterface::SCOPE_STORE
        )) {
            $scripts = '';
            $scriptOpen = '<script';
            $scriptClose = '</script>';
            $scriptOpenPos = strpos($content, $scriptOpen);

            while ($scriptOpenPos !== false) {
                $scriptClosePos = strpos($content, $scriptClose, $scriptOpenPos);
                $script = substr($content, $scriptOpenPos, $scriptClosePos - $scriptOpenPos + strlen($scriptClose));

                if (strpos($script, 'text/x-magento-template') !== false) {
                    $scriptOpenPos = strpos($content, $scriptOpen, $scriptClosePos);
                    continue;
                }

                $scripts .= "\n" . $script;
                $content = str_replace($script, '', $content);
                // Script cut out, continue search from its position.
                $scriptOpenPos = strpos($content, $scriptOpen, $scriptOpenPos);
            }

            if ($scripts) {
                $content = str_replace($bodyClose, $scripts . "\n" . $bodyClose, $content);
                $subject->setContent($content);
            }
        }
    }
}
