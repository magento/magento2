<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Sales\Test\Page\SalesGuestView;
use Mtf\Client\Browser;
use Mtf\TestStep\TestStepInterface;

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
     * @var Browser
     */
    protected $browser;

    /**
     * @constructor
     * @param SalesGuestView $salesGuestView
     * @param Browser $browser
     */
    public function __construct(SalesGuestView $salesGuestView, Browser $browser)
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
        $this->salesGuestView->getActionsToolbar()->clickLink('Print Order');
        $this->browser->selectWindow();
    }
}
