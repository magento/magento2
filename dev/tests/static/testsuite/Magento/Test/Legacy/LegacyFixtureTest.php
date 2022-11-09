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
        $docUrl = 'https://devdocs.magento.com/guides/v2.4/test/integration/parameterized_data_fixture.html';
        $files = AddedFiles::getAddedFilesList(__DIR__ . '/..');
        $legacyFixtureFiles = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php'
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
