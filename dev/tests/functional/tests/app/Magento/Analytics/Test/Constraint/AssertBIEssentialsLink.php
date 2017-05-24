<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert BI Essentials Sign Up page is opened by admin menu link
 */
class AssertBIEssentialsLink extends AbstractConstraint
{
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
     * @return void
     */
    public function processAssert(BrowserInterface $browser, $businessIntelligenceLink)
    {
        $this->browser = $browser;
        $this->browser->selectWindow();
        \PHPUnit_Framework_Assert::assertTrue(
            $this->browser->waitUntil(
                function () use ($businessIntelligenceLink) {
                    return ($this->browser->getUrl() === $businessIntelligenceLink) ?: null;
                }
            ),
            'BI Essentials Sign Up page was not opened by link.'
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
