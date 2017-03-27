<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert Basic Tier Sign Up page is opened by admin menu link
 */
class AssertBasicTierLink extends AbstractConstraint
{
    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Assert Basic Tier Sign Up page is opened by link
     *
     * @param BrowserInterface $browser
     * @param string $businessIntelligenceLink
     * @return void
     */
    public function processAssert(BrowserInterface $browser, $businessIntelligenceLink)
    {
        $this->browser = $browser;
        $this->browser->selectWindow();
        \PHPUnit_Framework_Assert::assertEquals(
            $businessIntelligenceLink,
            $this->browser->getUrl(),
            'Basic Tier Sign Up page was not opened by link.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Basic Tier Sign Up page is opened by link';
    }
}
