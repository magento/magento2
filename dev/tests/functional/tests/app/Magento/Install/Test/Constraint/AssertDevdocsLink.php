<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Install\Test\Page\DevdocsInstall;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check Developer Documentation link.
 */
class AssertDevdocsLink extends AbstractConstraint
{
    /**
     * Developer Documentation title.
     */
    const DEVDOCS_TITLE_TEXT = 'Setup Wizard installation';

    /**
     * Check Developer Documentation link.
     *
     * @param DevdocsInstall $devdocsInstallPage
     * @return void
     */
    public function processAssert(DevdocsInstall $devdocsInstallPage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::DEVDOCS_TITLE_TEXT,
            $devdocsInstallPage->getDevdocsBlock()->getDevdocsTitle(),
            'Developer Documentation link is wrong.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Developer Documentation link is correct.";
    }
}
