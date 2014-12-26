<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Install\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Install\Test\Page\Install;
use Magento\Install\Test\Fixture\Install as InstallConfig;

/**
 * Assert that selected encryption key displays on success full install page.
 */
class AssertKeyCreated extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

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
