<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert backend page title and it's availability.
 */
class AssertBackendPageIsAvailable extends AbstractConstraint
{
    const ERROR_TEXT = '404 Error';

    /**
     * Assert that backend page has correct title and 404 Error is absent on the page.
     *
     * @param Dashboard $dashboard
     * @param string $pageTitle
     * @return void
     */
    public function processAssert(Dashboard $dashboard, $pageTitle)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $pageTitle,
            $dashboard->getTitleBlock()->getTitle(),
            'Invalid page title is displayed.'
        );
        \PHPUnit_Framework_Assert::assertNotContains(
            self::ERROR_TEXT,
            $dashboard->getErrorBlock()->getContent(),
            "404 Error is displayed on '$pageTitle' page."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Backend has correct title and 404 page content is absent.';
    }
}
