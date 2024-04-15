<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\GraphQl;

use Exception;
use Magento\Framework\App\Utility\Files;
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
     * @var mixed
     */
    private static mixed $frontendUIComponent;

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
    public function testModulesRequireGraphQLChange(): void
    {
        $this->markTestSkipped('AC-9497 written incorrectly');
        $modulesRequireGraphQLChange = self::getModulesRequiringGraphQLChange();
        $graphQlModules = implode(", ", $modulesRequireGraphQLChange);
        $this->assertEmpty(
            $modulesRequireGraphQLChange,
            "The view layer changes have been detected in the " .
            str_replace("GraphQl", "", $graphQlModules) . " module. " .
            "The " . $graphQlModules ." module is expected to be updated to reflect these changes. " .
            "The test failure can be ignored if the changes can not be covered with GraphQL API."
        );
    }

    /**
     * returns a array with the list of graphql modules which require changes
     *
     * @return array
     * @throws Exception
     */
    private static function getModulesRequiringGraphQLChange(): array
    {
        $whitelistFiles = PHPCodeTest::getWhitelist(
            ['php', 'graphqls'],
            '',
            '',
            '/_files/whitelist/graphql.txt'
        );
        $fileList = self::filterFiles($whitelistFiles);

        $updatedGraphQlModules = [];
        $requireGraphQLChanges = [];
        foreach ($fileList as $whitelistFile) {
            $moduleName = self::getModuleName($whitelistFile);

            if (!$moduleName) {
                continue;
            }

            $isGraphQlModule = str_ends_with($moduleName, 'GraphQl');
            if (!in_array($moduleName, $updatedGraphQlModules) && $isGraphQlModule) {
                $updatedGraphQlModules[] = $moduleName;
                continue;
            }

            if (!in_array($moduleName, $requireGraphQLChanges) && self::isViewLayerClass($whitelistFile, $moduleName)) {
                $requireGraphQLChanges[] = $moduleName . "GraphQl";
            }
        }
        return array_diff($requireGraphQLChanges, $updatedGraphQlModules);
    }

    /**
     * Returns the module name of the file from the path
     *
     * @param string $filePath
     * @return string
     */
    private static function getModuleName(string $filePath): string
    {
        $fileName = substr($filePath, strlen(self::$changeCheckDir));
        $pathParts = explode('/', $fileName);

        return $pathParts[1] ?? '';
    }

    /**
     * Return true if the class is a data provider for the frontend
     *
     * @param string $filePath
     * @param string $moduleName
     * @return bool
     */
    private static function isViewLayerClass(string $filePath, string $moduleName): bool
    {
        $className = self::getClassNameWithNamespace($filePath);
        $adminChange = str_contains(strtolower($className), 'adminhtml');
        if ($className && !$adminChange && self::isFrontendUIComponent($moduleName, $className)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the files namespace using regular expression
     *
     * @param string $filePath
     * @return string
     */
    private static function getClassNameWithNamespace(string $filePath): string
    {
        $className = str_replace('.php', '', basename($filePath));
        if (preg_match('#^namespace\s+(.+?);$#sm', file_get_contents($filePath), $m)) {
            return ($m[1] && $className) ? $m[1] . "\\" . $className : '';
        }
        return '';
    }

    /**
     * Check if the class is a frontend data provider
     *
     * @param string $moduleName
     * @param string $className
     * @return bool
     */
    private static function isFrontendUIComponent(string $moduleName, string $className): bool
    {
        if (!isset(self::$frontendUIComponent[$moduleName])) {
            $files = glob(BP . '/app/code/Magento/'.$moduleName.'/view/frontend/*/*.xml');

            if (is_array($files)) {
                $uIComponentClasses = [];

                foreach ($files as $filename) {
                    $uIComponentClasses[] = simplexml_load_file($filename)->xpath('//@class');
                }
                self::$frontendUIComponent[$moduleName] = self::filterUiComponents(
                    array_unique(array_merge([], ...$uIComponentClasses)),
                    $moduleName
                );
            }
        }
        return in_array($className, self::$frontendUIComponent[$moduleName]);
    }

    /**
     * Filter the array of classes to return only the classes in this module
     *
     * @param array $uIComponentClasses
     * @param string $moduleName
     * @return array
     */
    private static function filterUiComponents(array $uIComponentClasses, string $moduleName): array
    {
        $frontendUIComponent = [];
        foreach ($uIComponentClasses as $dataProvider) {
            $dataProviderClass = ltrim((string)$dataProvider->class, '\\');
            if (str_starts_with($dataProviderClass, 'Magento\\' . $moduleName)) {
                $frontendUIComponent[] = $dataProviderClass;
            }
        }
        return $frontendUIComponent;
    }

    /**
     * Skip files not requiring graphql side changes
     *
     * @param array $fileList
     * @return array
     * @throws Exception
     */
    private static function filterFiles(array $fileList): array
    {
        $denyListFiles = Files::init()->readLists(__DIR__ . '/_files/denylist/*.txt');

        $filter = function ($value) use ($denyListFiles) {
            return !in_array($value, $denyListFiles);
        };

        return array_filter($fileList, $filter);
    }
}
