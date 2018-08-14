<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Locales;

/**
 * Assert that Interface Locale field has correct options.
 */
class AssertInterfaceLocaleAvailableOptions extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that Interface Locale field has correct options depends on magento mode.
     *
     * @param Locales $locales utility for work with locales
     * @param array $dropdownLocales array of interface locales
     */
    public function processAssert(
        Locales $locales,
        $dropdownLocales = []
    ) {
        if ($_ENV['mage_mode'] === 'production') {
            \PHPUnit_Framework_Assert::assertEquals(
                $locales->getList(Locales::TYPE_DEPLOYED),
                $dropdownLocales
            );
        } else {
            \PHPUnit_Framework_Assert::assertEmpty(
                array_diff($dropdownLocales, $locales->getList(Locales::TYPE_ALL))
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Interface locales list has correct values.';
    }
}
