<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate through menu to the page.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-34874
 */
class NavigateMenuTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Prepare data for further test execution.
     *
     * @param BrowserInterface $browser
     * @return void
     */
    public function __inject(
        BrowserInterface $browser
    ) {
        $this->browser = $browser;
    }

    /**
     * Run menu navigation test.
     *
     * @param Dashboard $dashboard
     * @param string $menuItem
     * @param bool $waitMenuItemNotVisible
     * @param bool $waitForNewWindow
     * @return void
     */
    public function test(Dashboard $dashboard, $menuItem, $waitMenuItemNotVisible = true, $waitForNewWindow = false)
    {
        $dashboard->open();
        $windowsCountBeforeClick = count($this->browser->getWindowHandles());
        $dashboard->getMenuBlock()->navigate($menuItem, $waitMenuItemNotVisible);
        if ($waitForNewWindow) {
            $this->browser->waitUntil(function () use ($windowsCountBeforeClick) {
                $windowsCount = count($this->browser->getWindowHandles());

                return $windowsCount > $windowsCountBeforeClick ? true : null;
            });
            sleep(10);
        }
    }
}
