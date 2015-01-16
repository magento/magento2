<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Template;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Newsletter template preview.
 */
class Preview extends Block
{
    /**
     * IFrame locator.
     *
     * @var string
     */
    protected $iFrame = '#preview_iframe';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '//ancestor::body/div[@data-role="loader"]';

    /**
     * Get page content text
     *
     * @return string
     */
    public function getPageContent()
    {
        $selector = $this->loader;
        $browser = $this->browser;
        $this->browser->waitUntil(
            function () use ($browser, $selector) {
                $element = $browser->find($selector, Locator::SELECTOR_XPATH);
                return $element->isVisible() == false ? true : null;
            }
        );
        $this->browser->switchToFrame(new Locator($this->iFrame));
        return $this->_rootElement->getText();
    }
}
