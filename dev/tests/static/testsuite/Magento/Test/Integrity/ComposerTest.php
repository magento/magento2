<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Composer\MagentoComponent;

/**
 * A test that enforces validity of composer.json files and any other conventions in Magento components
 */
class ComposerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var string
     */
    private static $root;

    /**
     * @var \stdClass
     */
    private static $rootJson;

    /**
     * @var array
     */
    private static $dependencies;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private static $objectManager;

    /**
     * @var string[]
     */
    private static $rootComposerModuleBlacklist = [];

    /**
     * @var string[]
     */
    private static $moduleNameBlacklist;

    /**
     * @var string
     */
    private static $magentoFrameworkLibraryName = 'magento/framework';

    public static function setUpBeforeClass(): void
    {
        self::$root = BP;
        self::$rootJson = json_decode(file_get_contents(self::$root . '/composer.json'), true);
        self::$dependencies = [];
        self::$objectManager = Bootstrap::create(BP, $_SERVER)->getObjectManager();
        // A block can be whitelisted and thus not be required to be public
        self::$rootComposerModuleBlacklist = self::getBlacklist(
            __DIR__ . '/_files/blacklist/composer_root_modules*.txt'
        );
        self::$moduleNameBlacklist = self::getBlacklist(__DIR__ . '/_files/blacklist/composer_module_names*.txt');
    }

    /**
     * Return aggregated blacklist
     *
     * @param string $pattern
     * @return string[]
     */
    public static function getBlacklist(string $pattern)
    {
        $blacklist = [];
        foreach (glob($pattern) as $list) {
            $blacklist[] = file($list, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
        return array_merge([], ...$blacklist);
    }

    public function testValidComposerJson()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * @param string $dir
             * @param string $packageType
             */
            function ($dir, $packageType) {
                $file = $dir . '/composer.json';
                $this->assertFileExists($file);
                $this->validateComposerJsonFile($dir);
                $contents = file_get_contents($file);
                $json = json_decode($contents);
                $this->assertCodingStyle($contents);
                $this->assertMagentoConventions($dir, $packageType, $json);
            },
            $this->validateComposerJsonDataProvider()
        );
    }

    /**
     * @return array
     */
    public function validateComposerJsonDataProvider()
    {
        $root = BP;
        $componentRegistrar = new ComponentRegistrar();
        $result = [];
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $dir) {
            $result[$dir] = [$dir, 'magento2-module'];
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE) as $dir) {
            $result[$dir] = [$dir, 'magento2-language'];
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::THEME) as $dir) {
            $result[$dir] = [$dir, 'magento2-theme'];
        }
        foreach ($componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $dir) {
            $result[$dir] = [$dir, 'magento2-library'];
        }
        $result[$root] = [$root, 'project'];

        return $result;
    }

    /**
     * Validate a composer.json under the given path
     *
     * @param string $path path to composer.json
     */
    private function validateComposerJsonFile($path)
    {
        /** @var \Magento\Framework\Composer\MagentoComposerApplicationFactory $appFactory */
        $appFactory = self::$objectManager->get(\Magento\Framework\Composer\MagentoComposerApplicationFactory::class);
        $app = $appFactory->create();

        try {
            $app->runComposerCommand(['command' => 'validate'], $path);
        } catch (\RuntimeException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    /**
     * Some of coding style conventions
     *
     * @param string $contents
     */
    private function assertCodingStyle($contents)
    {
        $this->assertDoesNotMatchRegularExpression(
            '/" :\s*["{]/',
            $contents,
            'Coding style: there should be no space before colon.'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/":["{]/',
            $contents,
            'Coding style: a space is necessary after colon.'
        );
    }

    /**
     * Enforce Magento-specific conventions to a composer.json file
     *
     * @param string $dir
     * @param string $packageType
     * @param \StdClass $json
     * @throws \InvalidArgumentException
     */
    private function assertMagentoConventions($dir, $packageType, \StdClass $json)
    {
        $this->assertObjectHasAttribute('name', $json);
        $this->assertObjectHasAttribute('license', $json);
        $this->assertObjectHasAttribute('type', $json);
        $this->assertObjectHasAttribute('require', $json);
        $this->assertEquals($packageType, $json->type);
        if ($packageType !== 'project') {
            self::$dependencies[] = $json->name;
            $this->assertAutoloadRegistrar($json, $dir);
            $this->assertNoMap($json);
        }
        switch ($packageType) {
            case 'magento2-module':
                $xml = simplexml_load_file("$dir/etc/module.xml");
                if ($this->isVendorMagento($json->name)) {
                    $this->assertConsistentModuleName($xml, $json->name);
                }
                $this->assertDependsOnPhp($json->require);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                $this->assertAutoload($json);
                $this->assertNoVersionSpecified($json);
                break;
            case 'magento2-language':
                $this->assertMatchesRegularExpression(
                    '/^magento\/language\-[a-z]{2}_([a-z]{4}_)?[a-z]{2}$/',
                    $json->name
                );
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                $this->assertNoVersionSpecified($json);
                break;
            case 'magento2-theme':
                $this->assertMatchesRegularExpression(
                    '/^magento\/theme-(?:adminhtml|frontend)(\-[a-z0-9_]+)+$/',
                    $json->name
                );
                $this->assertDependsOnPhp($json->require);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                $this->assertNoVersionSpecified($json);
                break;
            case 'magento2-library':
                $this->assertDependsOnPhp($json->require);
                $this->assertMatchesRegularExpression('/^magento\/framework*/', $json->name);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertRequireInSync($json);
                $this->assertAutoload($json);
                $this->assertNoVersionSpecified($json);
                break;
            case 'project':
                $this->checkProject();
                $this->assertNoVersionSpecified($json);
                break;
            default:
                throw new \InvalidArgumentException("Unknown package type {$packageType}");
        }
    }

    /**
     * Checks if package vendor is Magento.
     *
     * @param string $packageName
     * @return bool
     */
    private function isVendorMagento(string $packageName): bool
    {
        return strpos($packageName, 'magento/') === 0;
    }

    /**
     * Assert that component registrar is autoloaded in composer json
     *
     * @param \StdClass $json
     * @param string $dir
     */
    private function assertAutoloadRegistrar(\StdClass $json, $dir)
    {
        $error = 'There must be an "autoload->files" node in composer.json of each Magento component.';
        $this->assertObjectHasAttribute('autoload', $json, $error);
        $this->assertObjectHasAttribute('files', $json->autoload, $error);
        $this->assertTrue(in_array("registration.php", $json->autoload->files), $error);
        $this->assertFileExists("$dir/registration.php");
    }

    /**
     * Version must not be specified in the root and package composer JSON files in Git.
     *
     * All versions are added by tools during release publication by version setter tool.
     *
     * @param \StdClass $json
     */
    private function assertNoVersionSpecified(\StdClass $json)
    {
        if (!in_array($json->name, self::$rootComposerModuleBlacklist)) {
            $errorMessage = 'Version must not be specified in the root and package composer JSON files in Git';
            $this->assertObjectNotHasAttribute('version', $json, $errorMessage);
        }
    }

    /**
     * Assert that there is PSR-4 autoload in composer json
     *
     * @param \StdClass $json
     */
    private function assertAutoload(\StdClass $json)
    {
        $errorMessage = 'There must be an "autoload->psr-4" section in composer.json of each Magento component.';
        $this->assertObjectHasAttribute('autoload', $json, $errorMessage);
        $this->assertObjectHasAttribute('psr-4', $json->autoload, $errorMessage);
    }

    /**
     * Assert that there is map in specified composer json
     *
     * @param \StdClass $json
     */
    private function assertNoMap(\StdClass $json)
    {
        $error = 'There is no "extra->map" node in composer.json of each Magento component.';
        $this->assertObjectNotHasAttribute('extra', $json, $error);
    }

    /**
     * Enforce package naming conventions for modules
     *
     * @param \SimpleXMLElement $xml
     * @param string $packageName
     */
    private function assertConsistentModuleName(\SimpleXMLElement $xml, $packageName)
    {
        if (!in_array($packageName, self::$moduleNameBlacklist)) {
            $moduleName = (string)$xml->module->attributes()->name;
            $expectedPackageName = $this->convertModuleToPackageName($moduleName);
            $this->assertEquals(
                $expectedPackageName,
                $packageName,
                "For the module '{$moduleName}', the expected package name is '{$expectedPackageName}'"
            );
        }
    }

    /**
     * Make sure a component depends on php version
     *
     * @param \StdClass $json
     */
    private function assertDependsOnPhp(\StdClass $json)
    {
        $this->assertObjectHasAttribute('php', $json, 'This component is expected to depend on certain PHP version(s)');
    }

    /**
     * Make sure a component depends on magento/framework component
     *
     * @param \StdClass $json
     */
    private function assertDependsOnFramework(\StdClass $json)
    {
        $this->assertObjectHasAttribute(
            self::$magentoFrameworkLibraryName,
            $json,
            'This component is expected to depend on ' . self::$magentoFrameworkLibraryName
        );
    }

    /**
     * Assert that PHP versions in root composer.json and Magento component's composer.json are not out of sync
     *
     * @param string $name
     * @param string $phpVersion
     */
    private function assertPhpVersionInSync($name, $phpVersion)
    {
        if (isset(self::$rootJson['require']['php'])) {
            $composerVersionsPattern = '{\s*\|\|?\s*}';
            $rootPhpVersions = preg_split($composerVersionsPattern, self::$rootJson['require']['php']);
            $modulePhpVersions = preg_split($composerVersionsPattern, $phpVersion);

            $this->assertEmpty(
                array_diff($rootPhpVersions, $modulePhpVersions),
                "PHP version {$phpVersion} in component {$name} is inconsistent with version "
                . self::$rootJson['require']['php'] . ' in root composer.json'
            );
        }
    }

    /**
     * Make sure requirements of components are reflected in root composer.json
     *
     * @param \StdClass $json
     * @return void
     */
    private function assertRequireInSync(\StdClass $json)
    {
        if (preg_match('/magento\/project-*/', self::$rootJson['name']) == 1) {
            return;
        }
        if (!in_array($json->name, self::$rootComposerModuleBlacklist) && isset($json->require)) {
            $this->checkPackageInRootComposer($json);
        }
    }

    /**
     * Check if package is reflected in root composer.json
     *
     * @param \StdClass $json
     * @return void
     */
    private function checkPackageInRootComposer(\StdClass $json)
    {
        $name = $json->name;
        $errors = [];
        foreach (array_keys((array)$json->require) as $depName) {
            if ($depName == 'magento/magento-composer-installer') {
                // Magento Composer Installer is not needed for already existing components
                continue;
            }
            if (!isset(self::$rootJson['require-dev'][$depName]) && !isset(self::$rootJson['require'][$depName])
                && !isset(self::$rootJson['replace'][$depName])) {
                $errors[] = "'$name' depends on '$depName'";
            }
        }
        if (!empty($errors)) {
            $this->fail(
                "The following dependencies are missing in root 'composer.json',"
                . " while declared in child components.\n"
                . "Consider adding them to 'require-dev' section (if needed for child components only),"
                . " to 'replace' section (if they are present in the project),"
                . " to 'require' section (if needed for the skeleton).\n"
                . join("\n", $errors)
            );
        }
    }

    /**
     * Convert a fully qualified module name to a composer package name according to conventions
     *
     * @param string $moduleName
     * @return string
     */
    private function convertModuleToPackageName($moduleName)
    {
        list($vendor, $name) = explode('_', $moduleName, 2);
        $package = 'module';
        foreach (preg_split('/([A-Z\d][a-z]*)/', $name, -1, PREG_SPLIT_DELIM_CAPTURE) as $chunk) {
            $package .= $chunk ? "-{$chunk}" : '';
        }
        return strtolower("{$vendor}/{$package}");
    }

    public function testComponentPathsInRoot()
    {
        if (!isset(self::$rootJson['extra']) || !isset(self::$rootJson['extra']['component_paths'])) {
            $this->markTestSkipped("The root composer.json file doesn't mention any extra component paths information");
        }
        $this->assertArrayHasKey(
            'replace',
            self::$rootJson,
            "If there are any component paths specified, then they must be reflected in 'replace' section"
        );
        $flat = $this->getFlatPathsInfo(self::$rootJson['extra']['component_paths']);
        foreach ($flat as $item) {
            list($component, $path) = $item;
            $this->assertFileExists(
                self::$root . '/' . $path,
                "Missing or invalid component path: {$component} -> {$path}"
            );
            $this->assertArrayHasKey(
                $component,
                self::$rootJson['replace'],
                "The {$component} is specified in 'extra->component_paths', but missing in 'replace' section"
            );
        }
        foreach (array_keys(self::$rootJson['replace']) as $replace) {
            if (!MagentoComponent::matchMagentoComponent($replace)) {
                $this->assertArrayHasKey(
                    $replace,
                    self::$rootJson['extra']['component_paths'],
                    "The {$replace} is specified in 'replace', but missing in 'extra->component_paths' section"
                );
            }
        }
    }

    /**
     * @param array $info
     * @return array
     * @throws \Exception
     */
    private function getFlatPathsInfo(array $info)
    {
        $flat = [];
        foreach ($info as $key => $element) {
            if (is_string($element)) {
                $flat[] = [$key, $element];
            } elseif (is_array($element)) {
                foreach ($element as $path) {
                    $flat[] = [$key, $path];
                }
            } else {
                throw new \Exception("Unexpected element 'in extra->component_paths' section");
            }
        }

        return $flat;
    }

    /**
     * @return void
     */
    private function checkProject()
    {
        sort(self::$dependencies);
        $dependenciesListed = [];
        if (strpos(self::$rootJson['name'], 'magento/project-') !== 0) {
            $this->assertArrayHasKey(
                'replace',
                (array)self::$rootJson,
                'No "replace" section found in root composer.json'
            );
            foreach (array_keys((array)self::$rootJson['replace']) as $key) {
                if (MagentoComponent::matchMagentoComponent($key)) {
                    $dependenciesListed[] = $key;
                }
            }
            sort($dependenciesListed);
            $nonDeclaredDependencies = array_diff(
                self::$dependencies,
                $dependenciesListed,
                self::$rootComposerModuleBlacklist
            );
            $nonexistentDependencies = array_diff($dependenciesListed, self::$dependencies);
            $this->assertEmpty(
                $nonDeclaredDependencies,
                'Following dependencies are not declared in the root composer.json: '
                . join(', ', $nonDeclaredDependencies)
            );
            $this->assertEmpty(
                $nonexistentDependencies,
                'Following dependencies declared in the root composer.json do not exist: '
                . join(', ', $nonexistentDependencies)
            );
        }
    }

    /**
     * Check the correspondence between the root composer file and magento/framework composer file.
     */
    public function testConsistencyOfDeclarationsInComposerFiles()
    {
        if (strpos(self::$rootJson['name'], 'magento/project-') !== false) {
            // The Dependency test is skipped for vendor/magento build
            self::markTestSkipped(
                'The build is running for composer installation. Consistency test for composer files is skipped.'
            );
        }

        $componentRegistrar = new ComponentRegistrar();
        $magentoFrameworkLibraryDir =
            $componentRegistrar->getPath(ComponentRegistrar::LIBRARY, self::$magentoFrameworkLibraryName);
        $magentoFrameworkComposerFile =
            json_decode(
                file_get_contents($magentoFrameworkLibraryDir . DIRECTORY_SEPARATOR . 'composer.json'),
                true
            );

        $inconsistentDependencies = [];
        foreach ($magentoFrameworkComposerFile['require'] as $dependency => $constraint) {
            if (isset(self::$rootJson['require'][$dependency])
                && self::$rootJson['require'][$dependency] !== $constraint
            ) {
                $inconsistentDependencies[] = $dependency;
            }
        }

        $this->assertEmpty(
            $inconsistentDependencies,
            'There is a discrepancy between the declared versions of the following modules in "'
            . self::$magentoFrameworkLibraryName . '" and the root composer.json: '
            . implode(', ', $inconsistentDependencies)
        );
    }
}
