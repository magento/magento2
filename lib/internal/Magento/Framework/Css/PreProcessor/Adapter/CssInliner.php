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
        $this->emogrifier = new Emogrifier();
    }

    /**
     * Sets the HTML to be used with the css.
     * This method should be used with setCss.
     *
     * @param string $html
     * @return void
     */
    public function setHtml($html)
    {
        $this->emogrifier->setHtml($html);
    }

    /**
     * Sets the CSS to be merged with the HTML.
     * This method should be used with setHtml.
     *
     * @param string $css
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
     * Processes the html by placing the css inline.
     * You must set first the css by using setCss and html by using setHtml.
     *
     * @return string
     * @throws \BadMethodCallException
     */
    public function process()
    {
        return $this->emogrifier->emogrify();
    }
}
