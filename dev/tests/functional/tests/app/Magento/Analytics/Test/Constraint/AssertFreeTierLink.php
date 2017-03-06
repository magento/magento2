<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert Free Tier Sign Up page is opened by admin dashboard link
 */
class AssertFreeTierLink extends AbstractConstraint
{
    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert Free Tier Sign Up page is opened by link
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
            'Free Tier Sign Up page was not opened by link.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Free Tier Sign Up page is opened by link';
    }
}
