<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Install\Test\Page\Install;
use Magento\Install\Test\Fixture\Install as InstallConfig;

/**
 * Assert that selected encryption key displays on success full install page.
 */
class AssertKeyCreated extends AbstractConstraint
{
    /**
     * Assert that selected encryption key displays on success full install page.
     *
     * @param Install $installPage
     * @param InstallConfig $installConfig
     * @return void
     */
    public function processAssert(Install $installPage, InstallConfig $installConfig)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $installConfig->getKeyValue(),
            $installPage->getInstallBlock()->getAdminInfo()['encryption_key'],
            'Selected encryption key on install page not equals to data from fixture.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Selected encryption key displays on success full install page.';
    }
}
