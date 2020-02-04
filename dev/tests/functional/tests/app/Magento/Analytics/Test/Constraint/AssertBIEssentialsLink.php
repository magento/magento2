<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert BI Essentials Sign Up page is opened by admin menu link
 */
class AssertBIEssentialsLink extends AbstractConstraint
{
    /**
     * Count of try for choose menu item.
     */
    const MAX_TRY_COUNT = 2;

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert BI Essentials Sign Up page is opened by link
     *
     * @param BrowserInterface $browser
     * @param string $businessIntelligenceLink
     * @param Dashboard $dashboard
     * @param string $menuItem
     * @param bool $waitMenuItemNotVisible
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        $businessIntelligenceLink,
        Dashboard $dashboard,
        $menuItem,
        $waitMenuItemNotVisible = false
    ) {
        /**
         * In the parallel run case new windows that adding to selenium grid windows handler
         * are in competition with another windows in another browsers in the same selenium grid.
         * During this case need to have some algorithm for retrying some operations that changed
         * current window for browser, because it's some times happens.
         */
        $this->browser = $browser;
        $count = 0;
        $isVisible = false;
        do {
            try {
                $this->browser->selectWindow();
                $isVisible = $this->browser->waitUntil(function () use ($businessIntelligenceLink) {
                    return ($this->browser->getUrl() === $businessIntelligenceLink) ?: null;
                });
                break;
            } catch (\Throwable $e) {
                $dashboard->open();
                $dashboard->getMenuBlock()->navigate($menuItem, $waitMenuItemNotVisible);
                $count++;
            }
        } while ($count < self::MAX_TRY_COUNT);

        \PHPUnit\Framework\Assert::assertTrue(
            $isVisible,
            "BI Essentials Sign Up page was not opened by link.\n
                Actual link is '{$this->browser->getUrl()}'\n
                Expected link is '$businessIntelligenceLink'"
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'BI Essentials Sign Up page is opened by link';
    }
}
