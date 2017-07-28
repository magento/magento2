<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Refresh page step.
 */
class RefreshPageStep implements TestStepInterface
{
    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Refresh option.
     *
     * @var boolean
     */
    private $refresh;

    /**
     * @constructor
     * @param BrowserInterface $browser
     * @param boolean $refresh [optional]
     */
    public function __construct(BrowserInterface $browser, $refresh = false)
    {
        $this->browser = $browser;
        $this->refresh = $refresh;
    }

    /**
     * Refresh page.
     *
     * @return void
     */
    public function run()
    {
        if ($this->refresh) {
            $this->browser->refresh();
        }
    }
}
