<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Advanced Reporting section is visibile.
 */
class AssertAdvancedReportingSectionVisible extends AbstractConstraint
{
    /**
     * Assert Advanced Reporting section is visibile.
     *
     * @param SystemConfigEdit $configEdit
     * @return void
     */
    public function processAssert(SystemConfigEdit $configEdit)
    {
        $configEdit->open();
        \PHPUnit_Framework_Assert::assertTrue(
            in_array('Advanced Reporting', $configEdit->getTabs()->getSubTabsNames('General')),
            'Advanced Reporting section is not visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Advanced Reporting section is visible.';
    }
}
