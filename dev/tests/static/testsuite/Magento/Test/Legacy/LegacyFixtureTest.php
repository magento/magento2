<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Legacy;

use Magento\TestFramework\Utility\AddedFiles;
use PHPUnit\Framework\TestCase;

/**
 * Static test for parameterized data fixtures
 */
class LegacyFixtureTest extends TestCase
{
    /**
     * Prevent creating new fixture files
     *
     * @return void
     */
    public function testNew(): void
    {
        $docUrl = 'https://developer.adobe.com/commerce/testing/guide/integration/attributes/data-fixture/';
        $files = AddedFiles::getAddedFilesList(__DIR__ . '/..');
        $legacyFixtureFiles = [];
        //pattern to ignore skip and filter files
        $skip_pattern = '/(.*(filter|skip)-list(_ee|_b2b|).php)/';
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php'
                && !preg_match($skip_pattern, $file)
                && (
                    preg_match('/(integration\/testsuite|api-functional\/testsuite).*\/(_files|Fixtures)/', $file)
                    // Cover the case when tests are located in the module folder instead of dev/tests.
                    // for instance inventory
                    || (
                        strpos($file, 'dev/tests/') === false
                        && preg_match('/app\/code\/.*\/Test.*\/(_files|Fixtures)/', $file)
                        && !preg_match('/app\/code\/.*\/Tests?\/Performance\/(_files|Fixtures)/', $file)
                    )
                )
            ) {
                $legacyFixtureFiles[] = str_replace(BP . '/', '', $file);
            }
        }

        $this->assertCount(
            0,
            $legacyFixtureFiles,
            "The format used for creating fixtures is deprecated. Please use parameterized fixture format.\n"
            . "For details please look at $docUrl.\r\n"
            . "The following fixture files were added:\r\n"
            . implode(PHP_EOL, $legacyFixtureFiles)
        );
    }
}
