<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Secured protocol format.
     *
     * @var string
     */
    private $securedProtocol = \Magento\Framework\HTTP\PhpEnvironment\Request::SCHEME_HTTPS;

    /**
     * Unsecured protocol format.
     *
     * @var string
     */
    private $unsecuredProtocol = \Magento\Framework\HTTP\PhpEnvironment\Request::SCHEME_HTTP;

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    private $browser;

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
        $this->assertUsedProtocol($this->securedProtocol);
        $this->assertDirectHttpUnavailable();
    }

    /**
     * Assert that specified protocol is used on current page.
     *
     * @param string $expectedProtocol
     * @return void
     */
    private function assertUsedProtocol($expectedProtocol)
    {
        if (substr($expectedProtocol, -3) !== "://") {
            $expectedProtocol .= '://';
        }

        \PHPUnit_Framework_Assert::assertStringStartsWith(
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
    private function assertDirectHttpUnavailable()
    {
        $fakeUrl = str_replace($this->securedProtocol, $this->unsecuredProtocol, $this->browser->getUrl());
        $this->browser->open($fakeUrl);
        \PHPUnit_Framework_Assert::assertStringStartsWith(
            $this->securedProtocol,
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
