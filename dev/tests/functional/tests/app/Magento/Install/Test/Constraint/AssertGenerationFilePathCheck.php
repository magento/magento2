<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Install\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\PathChecker;

/**
 * Assert that path of generated files is correct.
 */
class AssertGenerationFilePathCheck extends AbstractConstraint
{
    /**
     * Assert that path of generated files is correct.
     *
     * @param PathChecker $pathChecker
     * @return void
     */
    public function processAssert(PathChecker $pathChecker)
    {
        $existsPaths = [
            'generated/code',
            'generated/metadata',
            'generated/metadata/global.ser',
            'generated/metadata/adminhtml.ser',
            'generated/metadata/crontab.ser',
            'generated/metadata/frontend.ser',
            'generated/metadata/webapi_rest.ser',
            'generated/metadata/webapi_soap.ser',
        ];

        $nonExistsPaths = [
            'var/di',
            'var/generation'
        ];

        foreach ($existsPaths as $path) {
            \PHPUnit_Framework_Assert::assertTrue(
                $pathChecker->pathExists($path),
                'Path "' . $path . '" does not exist.'
            );
        }

        foreach ($nonExistsPaths as $path) {
            \PHPUnit_Framework_Assert::assertFalse(
                $pathChecker->pathExists($path),
                'Path "' . $path . '" exists.'
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
        return 'Path of generated files is correct.';
    }
}
