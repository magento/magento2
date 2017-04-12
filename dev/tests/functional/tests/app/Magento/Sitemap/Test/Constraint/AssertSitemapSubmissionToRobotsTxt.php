<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that robots.txt file is available and contains correct content.
 */
class AssertSitemapSubmissionToRobotsTxt extends AbstractConstraint
{
    /**
     * Error HTTP response code.
     */
    const HTTP_NOT_FOUND = '404 Not Found';

    /**
     * File path for "Robots txt".
     *
     * @var string
     */
    private $filename = 'robots.txt';

    /**
     * Assert that robots.txt is available and contains correct data.
     *
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(BrowserInterface $browser)
    {
        $browser->open($_ENV['app_frontend_url'] . $this->filename);
        \PHPUnit_Framework_Assert::assertNotEquals(
            self::HTTP_NOT_FOUND,
            $browser->getTitle(),
            'File ' . $this->filename . ' is not readable or not exists.'
        );

        $expectedRobotsContent = 'Sitemap: ' .  $_ENV['app_frontend_url'] . 'sitemap.xml';
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($browser->getHtmlSource(), $expectedRobotsContent) !== false,
            'File ' . $this->filename . ' contains incorrect data.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'File ' . $this->filename . ' contains correct content.';
    }
}
