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
        $files = AddedFiles::getAddedFilesList(__DIR__ . '/..');
        $legacyFixtureFiles = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php'
                && preg_match('/(Test|integration\/testsuite|api-functional\/testsuite).*\/(_files|Fixtures)/', $file)
            ) {
                $legacyFixtureFiles[] = str_replace(BP . '/', '', $file);
            }
        }

        $this->assertCount(
            0,
            $legacyFixtureFiles,
            "Fixture files are deprecated. Please use parameterized data fixtures.\r\n" .
            "The following fixture files were added:\r\n"
            . implode(PHP_EOL, $legacyFixtureFiles)
        );
    }
}
