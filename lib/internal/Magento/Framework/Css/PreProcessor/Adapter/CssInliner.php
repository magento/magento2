<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Css\PreProcessor\Adapter;

use Magento\Framework\App\State;
use Pelago\Emogrifier\CssInliner as EmogrifierCssInliner;
use Symfony\Component\CssSelector\Exception\ParseException;

/**
 * This class will inline the css of an html to each tag to be used for applications such as a styled email.
 */
class CssInliner
{
    /**
     * @var State
     */
    private $appState;

    /**
     * @var string
     */
    private $html = '';

    /**
     * @var string
     */
    private $css = '';

    /**
     * @var bool
     */
    private $disableStyleBlocksParsing = false;

    /**
     * @param State $appState
     */
    public function __construct(State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * Sets the HTML to be used with the css. This method should be used with setCss.
     *
     * @param string $html
     * @return void
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * Sets the CSS to be merged with the HTML. This method should be used with setHtml.
     *
     * @param string $css
     * @return void
     */
    public function setCss($css)
    {
        $this->css = $css;
    }

    /**
     * Disables the parsing of <style> blocks.
     *
     * @return void
     */
    public function disableStyleBlocksParsing()
    {
        $this->disableStyleBlocksParsing = true;
    }

    /**
     * Processes the html by placing the css inline. Set first the css by using setCss and html by using setHtml.
     *
     * @return string
     * @throws \BadMethodCallException
     * @throws ParseException
     */
    public function process()
    {
        $emogrifier = EmogrifierCssInliner::fromHtml($this->html);
        $emogrifier->setDebug($this->appState->getMode() === State::MODE_DEVELOPER);

        if ($this->disableStyleBlocksParsing) {
            $emogrifier->disableStyleBlocksParsing();
        }

        $emogrifier->inlineCss($this->css);

        return $emogrifier->render();
    }
}
