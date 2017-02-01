<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics empty Vertical can not be saved in Stores > Configuration > General > Analytics > General menu.
 */
class AssertEmptyVerticalCanNotBeSaved extends AbstractConstraint
{
    /**
     * Assert Analytics empty Vertical can not be saved in Stores > Configuration > General > Analytics > General menu.
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
            'There is no error message when saving empty vertical in configuration'
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
            'Empty Magento Analytics vertical can not be saved';
    }
}
