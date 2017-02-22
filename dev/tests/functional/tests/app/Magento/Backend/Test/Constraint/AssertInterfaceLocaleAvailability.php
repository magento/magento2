<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemAccount;
use Magento\Mtf\Util\Command\Locales;

class AssertInterfaceLocaleAvailability extends AbstractConstraint
{
    /**
     * @param Locales $locales
     * @param SystemAccount $systemAccount
     */
    public function processAssert(
        Locales $locales,
        SystemAccount $systemAccount
    ) {
        $dropdownLocales = $systemAccount->getForm()->getInterfaceLocaleOptions();

        if ($_ENV['mage_mode'] === 'production') {
            $deployedLocales = $locales->getDeployed();
        } else {
            $allLocales = $locales->getAll();
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
