<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

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
    private $browser;

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
        \PHPUnit\Framework\Assert::assertTrue(
            $this->browser->waitUntil(
                function () use ($advancedReportingLink) {
                    return ($this->browser->getUrl() === $advancedReportingLink) ? true : null;
                }
            ),
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
