<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\Adminhtml\CmsPageNew;
use Magento\Variable\Test\Fixture\SystemVariable;

/**
 * Check that access to variables block on CMS page in admin panel is restricted.
 */
class AssertCustomVariableRestrictedAccess extends AbstractConstraint
{
    /**
     * Assert that variables block is not visible.
     *
     * @param CmsPageNew $cmsPageNew
     * @param SystemVariable $systemVariable
     * @return void
     */
    public function processAssert(
        CmsPageNew $cmsPageNew,
        SystemVariable $systemVariable
    ) {
        $systemVariable->persist();
        $cmsPageNew->open();

        \PHPUnit_Framework_Assert::assertFalse(
            $cmsPageNew->getPageForm()->isVariablesBlockVisible(),
            'Access to system variables block is supposed to be restricted.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Access to system variables block is restricted and block is not visible.';
    }
}
