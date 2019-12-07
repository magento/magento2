<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Constraint;

use Magento\Search\Test\Page\Adminhtml\SynonymGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert that access to synonym group index page by direct url is restricted.
 */
class AssertSynonymRestrictedAccess extends AbstractConstraint
{
    /**
     * Access denied text.
     */
    const ACCESS_DENIED_TEXT = 'Sorry, you need permissions to view this content.';

    /**
     * Assert that access to synonym group index page is restricted.
     *
     * @param Dashboard $dashboard
     * @param SynonymGroupIndex $synonymGroupIndex
     * @return void
     */
    public function processAssert(Dashboard $dashboard, SynonymGroupIndex $synonymGroupIndex)
    {
        $synonymGroupIndex->open();

        \PHPUnit\Framework\Assert::assertContains(
            self::ACCESS_DENIED_TEXT,
            $dashboard->getErrorBlock()->getContent(),
            'Synonym group index page is available.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Access to synonym group index page by direct url is restricted.';
    }
}
