<?php
/**
 * Inline translation interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate;

interface InlineInterface
{
    /**
     * Returns additional html attribute if needed by client.
     *
     * @param mixed $tagName
     * @return mixed
     */
    public function getAdditionalHtmlAttribute($tagName = null);

    /**
     * Check if inline translates is allowed
     *
     * @return bool
     */
    public function isAllowed();

    /**
     * Replace translation templates with HTML fragments
     *
     * @param array|string $body
     * @param bool $isJson
     * @return \Magento\Framework\Translate\InlineInterface
     */
    public function processResponseBody(&$body, $isJson = false);

    /**
     * Retrieve Inline Parser instance
     *
     * @return Inline\ParserInterface
     */
    public function getParser();
}
