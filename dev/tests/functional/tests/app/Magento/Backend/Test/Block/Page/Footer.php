<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Page;

use Magento\Mtf\Block\Block;

/**
 * Footer backend block.
 */
class Footer extends Block
{
    /**
     * Copyright locator.
     *
     * @var string
     */
    protected $copyright = '.copyright';

    /**
     * Wait for copyright is visible on the page.
     *
     * @return void
     */
    public function waitCopyright()
    {
        $selector = $this->copyright;
        $browser = $this->browser;
        $browser->waitUntil(
            function () use ($browser, $selector) {
                return $browser->find($selector)->isVisible() ? true : null;
            }
        );
    }
}
