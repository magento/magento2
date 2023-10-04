<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\GraphQl;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer;
use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper;
use Magento\Test\Php\LiveCodeTest as PHPCodeTest;
use PHPUnit\Framework\TestCase;

/**
 * Set of tests for static code style
 */
class LiveCodeTest extends TestCase
{
    private const FILE_EXTENSION = 'graphqls';
    /**
     * @var string
     */
    private static $reportDir = '';

    /**
     * @var string
     */
    private static $changeCheckDir = '';

    /**
     * Setup basics for all tests
     */
    public static function setUpBeforeClass(): void
    {
        self::$reportDir = BP . '/dev/tests/static/report';
        if (!is_dir(self::$reportDir)) {
            mkdir(self::$reportDir, 0770);
        }

        self::$changeCheckDir = BP . '/app/code/Magento';
    }

    /**
     * Test GraphQL schema files code style using phpcs
     */
    public function testCodeStyle(): void
    {
        $reportFile = self::$reportDir . '/graphql_phpcs_report.txt';
        $codeSniffer = new CodeSniffer('Magento', $reportFile, new Wrapper());
        $codeSniffer->setExtensions([self::FILE_EXTENSION]);
        $result = $codeSniffer->run(PHPCodeTest::getWhitelist([self::FILE_EXTENSION]));
        $report = file_exists($reportFile) ? file_get_contents($reportFile) : '';
        $this->assertEquals(
            0,
            $result,
            "PHP Code Sniffer detected {$result} violation(s): " . PHP_EOL . $report
        );
    }

    /**
     * Test if there is corresponding GraphQL module change for each magento core modules
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCorrespondingGraphQlChangeExists(): void
    {
        $changedModules = PHPCodeTest::getChangedCoreModules(self::$changeCheckDir);

        // Check if for each module change, a graphQL module change happened
        foreach ($changedModules as $module => $fileStat) {

            if (str_ends_with($module, 'GraphQl')) {
                continue;
            }

            $fileChanged = $fileStat['filesChanged'] ||
                $fileStat['insertions'] ||
                $fileStat['deletions'] ||
                $fileStat['paramsChanged'];

            // check if there is a reasonable change happened in the module
            if ($fileChanged) {
                $this->assertArrayHasKey(
                    $module . 'GraphQl',
                    $changedModules,
                    $module . "'s corresponding GraphQl module change is missing"
                );

                if(isset($changedModules[$module . 'GraphQl'])) {

                    // assert if there is change in graphql module
                    $this->assertTrue(
                        (
                            $changedModules[$module . 'GraphQl']['filesChanged'] ||
                            $changedModules[$module . 'GraphQl']['insertions'] ||
                            $changedModules[$module . 'GraphQl']['deletions'] ||
                            $changedModules[$module . 'GraphQl']['paramsChanged']
                        ),
                        $module . "'s corresponding GraphQl module change is missing"
                    );
                }
            }
        }
    }
}
