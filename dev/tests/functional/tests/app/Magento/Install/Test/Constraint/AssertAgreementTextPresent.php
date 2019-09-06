<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Install\Test\Page\Install;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\TestFramework\Inspection\Exception;

/**
 * Check that agreement text present on Terms & Agreement page during install.
 */
class AssertAgreementTextPresent extends AbstractConstraint
{
    /**
     * Part of Default license agreement text.
     */
    const DEFAULT_LICENSE_AGREEMENT_TEXT = 'Open Software License ("OSL") v. 3.0';

    /**
     * Part of Default license agreement text.
     */
    const LICENSE_AGREEMENT_TEXT = 'END USER LICENSE AGREEMENT';

    /**
     * Assert that part of license agreement text is present on Terms & Agreement page.
     *
     * @param Install $installPage
     * @return void
     */
    public function processAssert(Install $installPage)
    {
        try {
            \PHPUnit\Framework\Assert::assertContains(
                self::LICENSE_AGREEMENT_TEXT,
                $installPage->getLicenseBlock()->getLicense(),
                'License agreement text is absent.'
            );
        } catch (\Exception $e) {
            \PHPUnit\Framework\Assert::assertContains(
                self::DEFAULT_LICENSE_AGREEMENT_TEXT,
                $installPage->getLicenseBlock()->getLicense(),
                'License agreement text is absent.'
            );
        }
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "License agreement text is present on Terms & Agreement page.";
    }
}
