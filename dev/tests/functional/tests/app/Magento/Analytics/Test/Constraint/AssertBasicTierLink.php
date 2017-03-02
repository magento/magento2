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
     * Basic Tier Sign Up page url
     */
    const BASIC_TIER_LINK = 'https://dashboard.rjmetrics.com/v2/magento/signup';

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
     * @return void
     */
    public function processAssert(BrowserInterface $browser)
    {
        $this->browser = $browser;
        $this->browser->selectWindow();
        \PHPUnit_Framework_Assert::assertEquals(
            self::BASIC_TIER_LINK,
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
