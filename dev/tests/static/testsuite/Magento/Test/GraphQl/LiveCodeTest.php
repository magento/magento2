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
     */
    public function testCorrespondingGraphQlChangeExists(): void
    {
        $changedModules = self::getChangedCoreModules();

        // Check if for each module change, a graphQL module change happened
        foreach ($changedModules as $changedModule) {

            // get the git diff status of the module files
            $fileStat = self::getGitDiff(self::$changeCheckDir . '/' . $changedModule);
            $fileChanged = $fileStat['insertions'] >= 5 || $fileStat['deletions'] >= 5;

            // check if there is a reasonable change happened in the module
            if ($fileChanged) {
                // get the git diff status of the graphQL module files
                $graphQlFileStat = self::getGitDiff(self::$changeCheckDir . '/' . $changedModule . 'GraphQl');

                // assert if there is change in graphql module
                $this->assertTrue(
                    $graphQlFileStat['insertions'] >= 1 || $graphQlFileStat['deletions'] >= 1,
                    $changedModule. "'s corresponding GraphQl module change is missing"
                );
            }
        }
    }

    /**
     * returns a multi array with the list of core and graphql modules names
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private static function getChangedCoreModules(): array
    {
        $whitelistFiles = PHPCodeTest::getWhitelist(['php', 'graphqls'], '', '', '/_files/whitelist/graphql.txt');

        $changedModules = [];
        foreach ($whitelistFiles as $whitelistFile) {
            $fileName = substr($whitelistFile, strlen(self::$changeCheckDir));
            $changedModule = explode('/', $fileName);

            $isGraphQlModule = str_ends_with($changedModule[1], 'GraphQl');
            $isGraphQlModuleExists = file_exists(self::$changeCheckDir . '/' . $changedModule[1] . 'GraphQl');

            if (!$isGraphQlModule && $isGraphQlModuleExists &&
                (
                    in_array($changedModule[2], ["Controller", "Model", "Block"]) ||
                    (($changedModule[2] == "Ui") && in_array($changedModule[3], ["Component", "DataProvider"]))
                )
            ) {
                $changedModules[] = $changedModule[1];
            }
        }

        return array_unique($changedModules);
    }

    /**
     * @param string $directory
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private static function getGitDiff($directory = ''): array
    {
        $shell = new \Magento\Framework\Shell(
            new \Magento\Framework\Shell\CommandRenderer()
        );

        $fileStat = explode(
            PHP_EOL,
            $shell->execute('git diff --stat ' . $directory)
        );

        $insertions = 0;
        $deletions = 0;
        $fileChanges = 0;
        if (array_key_exists(3, $fileStat)) {
            list($fileChanges, $insertions, $deletions) = explode(",", $fileStat[3]);
            $fileChanges = intval($fileChanges);
            $insertions = intval($insertions);
            $deletions = intval($deletions);
        }

        return [
            'fileChanges' => $fileChanges,
            'insertions' => $insertions,
            'deletions' => $deletions
        ];
    }
}
