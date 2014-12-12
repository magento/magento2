<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Translate\Inline;

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
     */
    public function processAjaxPost(array $translateParams);

    /**
     * Replace html body with translation wrapping.
     *
     * @param string $body
     * @return string
     */
    public function processResponseBodyString($body);

    /**
     * Returns the body content that is being parsed.
     *
     * @return string
     */
    public function getContent();

    /**
     * Sets the body content that is being parsed passed upon the passed in string.
     *
     * @param $content string
     * @return void
     */
    public function setContent($content);

    /**
     * Set flag about parsed content is Json
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsJson($flag);
}
