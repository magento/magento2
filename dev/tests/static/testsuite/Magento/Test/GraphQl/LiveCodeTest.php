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
        $modulesWithViewLayerChanges = self::getModulesWithViewLayerChanges();
        $changedGraphQlModules = self::getChangedGraphQlModules();

        // Check if for each module change, a graphQL module change happened
        foreach ($modulesWithViewLayerChanges as $module) {
            $this->assertArrayHasKey(
                $module . 'GraphQl',
                $changedGraphQlModules,
                $module . " module: Required GraphQL changes to module (".
                $module . "GraphQl) are not included in the pull request"
            );
        }
    }

    /**
     * returns a array with the list of modules having view later change
     *
     * @return array
     */
    private static function getModulesWithViewLayerChanges(): array
    {
        $whitelistFiles = PHPCodeTest::getWhitelist(['php'], '', '', '/_files/whitelist/graphql.txt');

        $affectedModules = [];
        foreach ($whitelistFiles as $whitelistFile) {
            $changedModule = self::getChangedModuleName($whitelistFile);

            $isGraphQlModule = str_ends_with($changedModule[1], 'GraphQl');
            $isGraphQlModuleExists = file_exists(self::$changeCheckDir . '/' . $changedModule[1] . 'GraphQl');

            if (!$isGraphQlModule && $isGraphQlModuleExists &&
                (
                    in_array($changedModule[2], ["Controller", "Model", "Block"]) ||
                    (($changedModule[2] == "Ui") && in_array($changedModule[3], ["Component", "DataProvider"]))
                )
            ) {
                $affectedModules[] = $changedModule[1];
            }
        }
        return $affectedModules;
    }

    /**
     * returns a array with the list of graphql module having changes
     *
     * @return array
     */
    private static function getChangedGraphQlModules(): array
    {
        $whitelistFiles = PHPCodeTest::getWhitelist(['php', 'graphqls'], '', '', '/_files/whitelist/graphql.txt');

        $affectedModules = [];
        foreach ($whitelistFiles as $whitelistFile) {
            $changedModule = self::getChangedModuleName($whitelistFile);

            $isGraphQlModule = str_ends_with($changedModule[1], 'GraphQl');

            if ($isGraphQlModule) {
                $affectedModules[] = $changedModule[1];
            }
        }
        return $affectedModules;
    }

    /**
     * @param string $whitelistFile
     * @return array
     */
    private static function getChangedModuleName($whitelistFile): array
    {
        $fileName = substr($whitelistFile, strlen(self::$changeCheckDir));
        return explode('/', $fileName);
    }
}
