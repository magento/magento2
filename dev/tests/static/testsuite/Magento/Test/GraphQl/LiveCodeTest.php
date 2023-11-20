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
     * @var array
     */
    private static $uiDataComponentInterface = [
        'Magento\Framework\App\ActionInterface',
        'Magento\Framework\View\Element\BlockInterface',
        'Magento\Framework\View\Element\UiComponentInterface',
        'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface',
    ];

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
     */
    private static function getModulesRequiringGraphQLChange(): array
    {
        $whitelistFiles = PHPCodeTest::getWhitelist(
            ['php', 'graphqls'],
            '',
            '',
            '/_files/whitelist/graphql.txt'
        );

        $updatedGraphQlModules = [];
        $requireGraphQLChanges = [];
        foreach ($whitelistFiles as $whitelistFile) {
            $moduleName = self::getModuleName($whitelistFile);

            if (!$moduleName) {
                continue;
            }

            $isGraphQlModule = str_ends_with($moduleName, 'GraphQl');
            if (!in_array($moduleName, $updatedGraphQlModules) && $isGraphQlModule) {
                $updatedGraphQlModules[] = $moduleName;
                continue;
            }

            if (!in_array($moduleName, $requireGraphQLChanges) && self::isViewLayerClass($whitelistFile)) {
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
     * Return true if the class implements any of the defined interfaces
     *
     * @param string $filePath
     * @return bool
     */
    private static function isViewLayerClass(string $filePath): bool
    {
        $className = self::getClassNameWithNamespace($filePath);
        if (!$className || str_contains(strtolower($className), 'adminhtml')) {
            return false;
        }

        $implementingInterfaces = array_values(class_implements($className));
        return !empty(array_intersect($implementingInterfaces, self::$uiDataComponentInterface));
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
}
