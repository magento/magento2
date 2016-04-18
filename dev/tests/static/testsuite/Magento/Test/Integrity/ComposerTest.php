<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Composer\MagentoComponent;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Shell;
use Magento\Framework\Exception\LocalizedException;

/**
 * A test that enforces validity of composer.json files and any other conventions in Magento components
 */
class ComposerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Shell
     */
    private static $shell;

    /**
     * @var bool
     */
    private static $isComposerAvailable;

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
     * @var string
     */
    private static $composerPath = 'composer';

    public static function setUpBeforeClass()
    {
        if (defined('TESTS_COMPOSER_PATH')) {
            self::$composerPath = TESTS_COMPOSER_PATH;
        }
        self::$shell = self::createShell();
        self::$isComposerAvailable = self::isComposerAvailable();
        self::$root = Files::init()->getPathToSource();
        self::$rootJson = json_decode(file_get_contents(self::$root . '/composer.json'), true);
        self::$dependencies = [];
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
        $root = \Magento\Framework\App\Utility\Files::init()->getPathToSource();
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
        if (self::$isComposerAvailable) {
            self::$shell->execute(self::$composerPath . ' validate --working-dir=%s', [$path]);
        }
    }

    /**
     * Some of coding style conventions
     *
     * @param string $contents
     */
    private function assertCodingStyle($contents)
    {
        $this->assertNotRegExp('/" :\s*["{]/', $contents, 'Coding style: no space before colon.');
        $this->assertNotRegExp('/":["{]/', $contents, 'Coding style: a space is necessary after colon.');
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
        $this->assertObjectHasAttribute('version', $json);
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
                $this->assertConsistentModuleName($xml, $json->name);
                $this->assertDependsOnPhp($json->require);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                $this->assertAutoload($json);
                break;
            case 'magento2-language':
                $this->assertRegExp('/^magento\/language\-[a-z]{2}_([a-z]{4}_)?[a-z]{2}$/', $json->name);
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                break;
            case 'magento2-theme':
                $this->assertRegExp('/^magento\/theme-(?:adminhtml|frontend)(\-[a-z0-9_]+)+$/', $json->name);
                $this->assertDependsOnPhp($json->require);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertDependsOnFramework($json->require);
                $this->assertRequireInSync($json);
                break;
            case 'magento2-library':
                $this->assertDependsOnPhp($json->require);
                $this->assertRegExp('/^magento\/framework*/', $json->name);
                $this->assertPhpVersionInSync($json->name, $json->require->php);
                $this->assertRequireInSync($json);
                $this->assertAutoload($json);
                break;
            case 'project':
                $this->checkProject();
                break;
            default:
                throw new \InvalidArgumentException("Unknown package type {$packageType}");
        }
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
        $moduleName = (string)$xml->module->attributes()->name;
        $this->assertEquals(
            $packageName,
            $this->convertModuleToPackageName($moduleName),
            "For the module '{$moduleName}', the expected package name is '{$packageName}'"
        );
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
            'magento/framework',
            $json,
            'This component is expected to depend on magento/framework'
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
            $this->assertEquals(
                self::$rootJson['require']['php'],
                $phpVersion,
                "PHP version {$phpVersion} in component {$name} is inconsistent with version "
                . self::$rootJson['require']['php'] . ' in root composer.json'
            );
        }
    }

    /**
     * Make sure requirements of components are reflected in root composer.json
     *
     * @param \StdClass $json
     */
    private function assertRequireInSync(\StdClass $json)
    {
        $name = $json->name;
        if (preg_match('/magento\/project-*/', self::$rootJson['name']) == 1) {
            return;
        }
        if (isset($json->require)) {
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
        foreach (preg_split('/([A-Z][a-z\d]+)/', $name, -1, PREG_SPLIT_DELIM_CAPTURE) as $chunk) {
            $package .= $chunk ? "-{$chunk}" : '';
        }
        return strtolower("{$vendor}/{$package}");
    }

    /**
     * Create shell wrapper
     *
     * @return \Magento\Framework\Shell
     */
    private static function createShell()
    {
        return new Shell(new Shell\CommandRenderer, null);
    }

    /**
     * Check if composer command is available in the environment
     *
     * @return bool
     */
    private static function isComposerAvailable()
    {
        try {
            self::$shell->execute(self::$composerPath . ' --version');
        } catch (LocalizedException $e) {
            return false;
        }
        return true;
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
        while (list(, list($component, $path)) = each($flat)) {
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
            $nonDeclaredDependencies = array_diff(self::$dependencies, $dependenciesListed);
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
}
