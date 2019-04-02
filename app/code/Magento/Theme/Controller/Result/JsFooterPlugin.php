<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Result;

/**
 * Plugin for putting messages to cookies
 */
class JsFooterPlugin
{
    /**
     * Put all javascript to footer before sending the response
     *
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        $content = $subject->getContent();
        $script = [];
        if (strpos($content, '</body') !== false) {
            $pattern = '#<script[^>]*+(?<!text/x-magento-template.)>.*?</script>#is';
            $content = preg_replace_callback(
                $pattern,
                function ($matchPart) use (&$script) {
                    $script[] = $matchPart[0];
                    return '';
                },
                $content
            );
            $subject->setContent(
                str_replace('</body', implode("\n", $script) . "\n</body", $content)
            );
        }
    }
}
