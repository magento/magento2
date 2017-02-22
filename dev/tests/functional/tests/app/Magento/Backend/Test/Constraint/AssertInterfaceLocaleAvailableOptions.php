<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\SystemAccount;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Locales;

/**
 * Assert that Interface Locale field has correct options.
 */
class AssertInterfaceLocaleAvailableOptions extends AbstractConstraint
{
    /**
     * @param Locales $locales
     * @param SystemAccount $systemAccount
     */
    public function processAssert(
        Locales $locales,
        SystemAccount $systemAccount
    ) {
        $dropdownLocales = $systemAccount->getForm()->getInterfaceLocales();
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
