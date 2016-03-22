<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Assert that Secure Urls Enabled.
 */
class AssertSecureUrlEnabled extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that Secure Urls Enabled.
     *
     * @param BrowserInterface $browser
     * @param Dashboard $dashboard
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        Dashboard $dashboard,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $dashboard->open();
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($browser->getUrl(), 'https://') !== false,
            'Secure Url is not displayed on backend.'
        );

        $customerAccountLogin->open();
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($browser->getUrl(), 'https://') !== false,
            'Secure Url is not displayed on frontend.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Secure Urls are displayed successful.';
    }
}
