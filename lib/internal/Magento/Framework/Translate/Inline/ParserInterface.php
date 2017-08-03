<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Inline;

/**
 * Processes the content with the inline translation replacement so the inline translate JavaScript code will work.
 *
 * @api
 * @since 2.0.0
 */
interface ParserInterface
{
    /**
     * Regular Expression for detected and replace translate
     */
    const REGEXP_TOKEN = '\{\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\}';

    /**
     * Parse and save edited translation
     *
     * @param array $translateParams
     * @return $this
     * @since 2.0.0
     */
    public function processAjaxPost(array $translateParams);

    /**
     * Replace html body with translation wrapping.
     *
     * @param string $body
     * @return string
     * @since 2.0.0
     */
    public function processResponseBodyString($body);

    /**
     * Returns the body content that is being parsed.
     *
     * @return string
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Sets the body content that is being parsed passed upon the passed in string.
     *
     * @param $content string
     * @return void
     * @since 2.0.0
     */
    public function setContent($content);

    /**
     * Set flag about parsed content is Json
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setIsJson($flag);
}
