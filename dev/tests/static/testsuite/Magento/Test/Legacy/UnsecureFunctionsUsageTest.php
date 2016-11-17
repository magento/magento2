<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Utility\FunctionDetector;

/**
 * Tests to detect unsecure functions usage
 */
class UnsecureFunctionsUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Lists of restricted entities from fixtures
     *
     * @var array
     */
    private static $phpUnsecureFunctions = [];

    /**
     * JS unsecure functions to detect
     *
     * @var array
     */
    private static $jsUnsecureFunctions = [];

    /**
     * Function replacements
     *
     * @var array
     */
    private static $functionReplacements = [];

    /**
     * Read fixtures into memory as arrays
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::_loadData(self::$phpUnsecureFunctions, 'unsecure_phpfunctions*.php');
        self::_loadData(self::$jsUnsecureFunctions, 'unsecure_jsfunctions*.php');
        foreach (self::$phpUnsecureFunctions as $functionName => $data) {
            self::$functionReplacements[$functionName] = $data['replacement'];
        }
        foreach (self::$jsUnsecureFunctions as $functionName => $data) {
            self::$functionReplacements[$functionName] = $data['replacement'];
        }
    }

    /**
     * Loads and merges data from fixtures
     *
     * @param array $data
     * @param string $filePattern
     * @return void
     */
    private static function _loadData(array &$data, $filePattern)
    {
        foreach (glob(__DIR__ . '/_files/security/' . $filePattern) as $file) {
            $data = array_merge_recursive($data, self::_readList($file));
        }
        $componentRegistrar = new ComponentRegistrar();
        foreach ($data as $key => $value) {
            $excludes = $value['exclude'];
            $excludePaths = [];
            foreach ($excludes as $exclude) {
                $excludePaths[] = $componentRegistrar->getPath($exclude['type'], $exclude['name'])
                    . '/' . $exclude['path'];
            }
            $data[$key]['exclude'] = $excludePaths;
        }
    }

    /**
     * Isolate including a file into a method to reduce scope
     *
     * @param string $file
     * @return array
     */
    private static function _readList($file)
    {
        return include $file;
    }

    /**
     * File extensions pattern to search for
     *
     * @var string
     */
    private $fileExtensions = '/\.(php|phtml|js)$/';

    /**
     * Detect unsecure functions usage for changed files in whitelist with the exception of blacklist
     *
     * @return void
     */
    public function testUnsecureFunctionsUsage()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $functionDetector = new FunctionDetector();
        $invoker(
            function ($fileName) use ($functionDetector) {
                $functions = $this->getFunctions($fileName);
                $lines = $functionDetector->detectFunctions($fileName, array_keys($functions));

                $message = '';
                if (!empty($lines)) {
                    $message = $this->composeMessage($fileName, $lines);
                }
                $this->assertEmpty(
                    $lines,
                    $message
                );
            },
            $this->getFilesToVerify()
        );
    }

    private function composeMessage($fileName, $lines)
    {
        $result = '';
        foreach ($lines as $lineNumber => $detectedFunctions) {
            $replacements = array_intersect_key(self::$functionReplacements, array_flip($detectedFunctions));
            $replacementString = '';
            foreach ($replacements as $function => $replacement) {
                $replacementString .= "\t\t'$function' => '$replacement'\n";
            }
            $result .= sprintf(
                "Functions '%s' are not secure in %s. \n\tSuggested replacement:\n%s",
                implode(', ', $detectedFunctions),
                $fileName . ':' . $lineNumber,
                $replacementString
            );
        }
        return $result;
    }

    /**
     * Get files to be verified
     *
     * @return array
     */
    private function getFilesToVerify()
    {
        $fileExtensions = $this->fileExtensions;
        $directoriesToScan = Files::init()->readLists(__DIR__ . '/_files/security/whitelist.txt');

        $filesToVerify = [];
        foreach (glob(__DIR__ . '/../_files/changed_files*') as $listFile) {
            $filesToVerify = array_merge(
                $filesToVerify,
                file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            );
        }
        array_walk(
            $filesToVerify,
            function (&$file) {
                $file = [BP . '/' . $file];
            }
        );
        $filesToVerify = array_filter(
            $filesToVerify,
            function ($path) use ($directoriesToScan, $fileExtensions) {
                if (!file_exists($path[0])) {
                    return false;
                }
                $path = realpath($path[0]);
                foreach ($directoriesToScan as $directory) {
                    $directory = realpath($directory);
                    if (strpos($path, $directory) === 0) {
                        if (preg_match($fileExtensions, $path)) {
                            // skip unit tests
                            if (preg_match('#' . preg_quote('Test/Unit', '#') . '#', $path)) {
                                return false;
                            }
                            return true;
                        }
                    }
                }
                return false;
            }
        );
        return $filesToVerify;
    }

    /**
     * Get functions by file extension
     *
     * @param string $fileName
     * @return array
     */
    private function getFunctions($fileName)
    {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $functions = [];
        if ($fileExtension == 'php') {
            $functions = self::$phpUnsecureFunctions;
        } elseif ($fileExtension == 'js') {
            $functions = self::$jsUnsecureFunctions;
        } elseif ($fileExtension == 'phtml') {
            $functions = self::$phpUnsecureFunctions + self::$jsUnsecureFunctions;
        }
        foreach ($functions as $function => $functionRules) {
            if (in_array($fileName, $functionRules['exclude'])) {
                unset($functions[$function]);
            }
        }
        return $functions;
    }
}
