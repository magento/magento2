<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Assert that Locale field has correct visibility.
 */
class AssertLocaleCodeVisibility extends AbstractConstraint
{
    /**
     * Assert Locale field visibility.
     *
     * @param SystemConfigEdit $configEdit
     * @return void
     */
    public function processAssert(SystemConfigEdit $configEdit)
    {
        if ($_ENV['mage_mode'] === 'production') {
            \PHPUnit\Framework\Assert::assertTrue(
                $configEdit->getForm()->getGroup('general', 'locale')->isFieldDisabled('general', 'locale', 'code'),
                'Locale field should be disabled in production mode.'
            );
        } else {
            \PHPUnit\Framework\Assert::assertFalse(
                $configEdit->getForm()->getGroup('general', 'locale')->isFieldDisabled('general', 'locale', 'code'),
                'Locale field should be not disabled in developer or default mode.'
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
        return 'Locale field has correct visibility.';
    }
}
