<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert that https protocol is used all over the Admin panel.
 */
class AssertHttpsUsedOnBackend extends AbstractConstraint
{
    /**
     * Protocols
     */
    const SCHEME_HTTP  = 'http';
    const SCHEME_HTTPS = 'https';

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Validations execution.
     *
     * @param BrowserInterface $browser
     * @param Dashboard $adminDashboardPage
     * @param string $navMenuPath
     * @return void
     */
    public function processAssert(BrowserInterface $browser, Dashboard $adminDashboardPage, $navMenuPath)
    {
        $this->browser = $browser;

        // Open specified Admin page using Navigation Menu to assert that JS is deployed validly as a part of statics.
        $adminDashboardPage->open()->getMenuBlock()->navigate($navMenuPath);
        $this->assertUsedProtocol(self::SCHEME_HTTPS);
        $this->assertDirectHttpUnavailable();
    }

    /**
     * Assert that specified protocol is used on current page.
     *
     * @param string $expectedProtocol
     * @return void
     */
    protected function assertUsedProtocol($expectedProtocol)
    {
        if (substr($expectedProtocol, -3) !== "://") {
            $expectedProtocol .= '://';
        }

        \PHPUnit\Framework\Assert::assertStringStartsWith(
            $expectedProtocol,
            $this->browser->getUrl(),
            "$expectedProtocol is not used."
        );
    }

    /**
     * Assert that Merchant is redirected to https if trying to access the page directly via http.
     *
     * @return void
     */
    protected function assertDirectHttpUnavailable()
    {
        $fakeUrl = str_replace(self::SCHEME_HTTPS, self::SCHEME_HTTP, $this->browser->getUrl());
        $this->browser->open($fakeUrl);
        \PHPUnit\Framework\Assert::assertStringStartsWith(
            self::SCHEME_HTTPS,
            $this->browser->getUrl(),
            'Merchant is not redirected to https if tries to access the Admin panel page directly via http.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Secured URLs are used for Admin panel pages.';
    }
}
