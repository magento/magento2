<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert Advanced Reporting Sign Up page is opened by admin dashboard link.
 */
class AssertAdvancedReportingPage extends AbstractConstraint
{
    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert Advanced Reporting Sign Up page is opened by link.
     *
     * @param BrowserInterface $browser
     * @param string $advancedReportingLink
     * @return void
     */
    public function processAssert(BrowserInterface $browser, $advancedReportingLink)
    {
        $this->browser = $browser;
        $this->browser->selectWindow();
        \PHPUnit_Framework_Assert::assertEquals(
            $advancedReportingLink,
            $this->browser->getUrl(),
            'Advanced Reporting Sign Up page was not opened by link.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Advanced Reporting Sign Up page is opened by link';
    }
}
