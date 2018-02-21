<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert empty industry can not be saved in advanced reporting configuration.
 */
class AssertEmptyIndustryCanNotBeSaved extends AbstractConstraint
{
    /**
     * Assert empty industry can not be saved in Advanced Reporting configuration.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param string $errorMessage
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics, $errorMessage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $errorMessage,
            $configAnalytics->getMessages()->getErrorMessage(),
            'There is no error message when saving empty industry in configuration'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return
            'Empty Magento Advanced Reporting industry can not be saved';
    }
}
