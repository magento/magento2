<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Install\Test\Constraint;

use Magento\Install\Test\Page\Install;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that PHP Version, PHP Extensions and File Permission are ok.
 */
class AssertSuccessfulReadinessCheck extends AbstractConstraint
{
    /**
     * PHP version message.
     */
    const PHP_VERSION_MESSAGE = 'Your PHP version is correct';

    /**
     * PHP extensions message.
     */
    const PHP_EXTENSIONS_REGEXP = '/You meet (\d+) out of \1 PHP extensions requirements\./';

    /**
     * File permission message.
     */
    const FILE_PERMISSION_REGEXP = '/You meet (\d+) out of \1 writable file permission requirements\./';

    /**
     * Assert that PHP Version, PHP Extensions and File Permission are ok.
     *
     * @param Install $installPage
     * @return void
     */
    public function processAssert(Install $installPage)
    {
        \PHPUnit_Framework_Assert::assertContains(
            self::PHP_VERSION_MESSAGE,
            $installPage->getReadinessBlock()->getPhpVersionCheck(),
            'PHP version is incorrect.'
        );
        \PHPUnit_Framework_Assert::assertRegExp(
            self::PHP_EXTENSIONS_REGEXP,
            $installPage->getReadinessBlock()->getPhpExtensionsCheck(),
            'PHP extensions missed.'
        );
        \PHPUnit_Framework_Assert::assertRegExp(
            self::FILE_PERMISSION_REGEXP,
            $installPage->getReadinessBlock()->getFilePermissionCheck(),
            'File permissions does not meet requirements.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "PHP Version, PHP Extensions and File Permission are ok.";
    }
}
