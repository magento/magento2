<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Pelago\Emogrifier;

/**
 * Adapter for Emogrifier 3rd party library
 */
class CssInliner
{
    /**
     * @var Emogrifier
     */
    private $emogrifier;

    public function __construct()
    {
        $this->emogrifier = new Emogrifier;
    }

    /**
     * Sets the HTML to emogrify.
     *
     * @param string $html the HTML to emogrify, must be UTF-8-encoded
     *
     * @return void
     */
    public function setHtml($html)
    {
        $this->emogrifier->setHtml($html);
    }

    /**
     * Sets the CSS to merge with the HTML.
     *
     * @param string $css the CSS to merge, must be UTF-8-encoded
     *
     * @return void
     */
    public function setCss($css)
    {
        /**
         * Adds space to CSS string before passing to Emogrifier to fix known parsing issue with library.
         * https://github.com/jjriv/emogrifier/issues/370
         */
        $cssWithAddedSpaces = preg_replace('#([\{\}>])#i', ' $1 ', $css);

        $this->emogrifier->setCss($cssWithAddedSpaces);
    }

    /**
     * Disables the parsing of <style> blocks.
     *
     * @return void
     */
    public function disableStyleBlocksParsing()
    {
        $this->emogrifier->disableStyleBlocksParsing();
    }

    /**
     * Applies $this->css to $this->html and returns the HTML with the CSS
     * applied.
     *
     * This method places the CSS inline.
     *
     * @return string
     *
     * @throws \BadMethodCallException
     */
    public function process()
    {
        return $this->emogrifier->emogrify();
    }
}
