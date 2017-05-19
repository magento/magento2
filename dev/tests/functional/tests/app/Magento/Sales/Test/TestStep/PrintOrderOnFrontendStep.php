<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\SalesGuestView;

/**
 * Click on "Print Order" button.
 */
class PrintOrderOnFrontendStep implements TestStepInterface
{
    /**
     * View orders page.
     *
     * @var SalesGuestView
     */
    protected $salesGuestView;

    /**
     * Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * @constructor
     * @param SalesGuestView $salesGuestView
     * @param BrowserInterface $browser
     */
    public function __construct(SalesGuestView $salesGuestView, BrowserInterface $browser)
    {
        $this->salesGuestView = $salesGuestView;
        $this->browser = $browser;
    }

    /**
     * Click on "Print Order" button.
     *
     * @return void
     */
    public function run()
    {
        $this->salesGuestView->getActionsToolbar()->clickLink('Print');
        $this->browser->selectWindow();
    }
}
