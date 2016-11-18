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
     * Php unsecure functions
     *
     * @var array
     */
    private static $phpUnsecureFunctions = [];

    /**
     * JS unsecure functions
     *
     * @var array
     */
    private static $jsUnsecureFunctions = [];

    /**
     * File extensions pattern to search for
     *
     * @var string
     */
    private $fileExtensions = '/\.(php|phtml|js)$/';

    /**
     * Read fixtures into memory as arrays
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadData(self::$phpUnsecureFunctions, 'unsecure_php_functions*.php');
        self::loadData(self::$jsUnsecureFunctions, 'unsecure_js_functions*.php');
    }

    /**
     * Loads and merges data from fixtures
     *
     * @param array $data
     * @param string $filePattern
     * @return void
     */
    private static function loadData(array &$data, $filePattern)
    {
        foreach (glob(__DIR__ . '/_files/security/' . $filePattern) as $file) {
            $data = array_merge_recursive($data, self::readList($file));
        }
        $componentRegistrar = new ComponentRegistrar();
        foreach ($data as $key => $value) {
            $excludes = $value['exclude'];
            $excludePaths = [];
            foreach ($excludes as $exclude) {
                if ('setup' == $exclude['type']) {
                    $excludePaths[] = BP . '/setup/' . $exclude['path'];
                } else {
                    $excludePaths[] = $componentRegistrar->getPath($exclude['type'], $exclude['name'])
                        . '/' . $exclude['path'];
                }
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
    private static function readList($file)
    {
        return include $file;
    }

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
            function ($fileFullPath) use ($functionDetector) {
                $functions = $this->getFunctions($fileFullPath);
                $lines = $functionDetector->detectFunctions($fileFullPath, array_keys($functions));

                $message = '';
                if (!empty($lines)) {
                    $message = $this->composeMessage($fileFullPath, $lines, $functions);
                }
                $this->assertEmpty(
                    $lines,
                    $message
                );
            },
            $this->getFilesToVerify()
        );
    }

    /**
     * Compose message
     *
     * @param string $fileFullPath
     * @param array $lines
     * @param array $functionRules
     * @return string
     */
    private function composeMessage($fileFullPath, $lines, $functionRules)
    {
        $result = '';
        foreach ($lines as $lineNumber => $detectedFunctions) {
            $detectedFunctionRules = array_intersect_key($functionRules, array_flip($detectedFunctions));
            $replacementString = '';
            foreach ($detectedFunctionRules as $function => $functionRule) {
                $replacement = $functionRule['replacement'];
                if (is_array($replacement)) {
                    $replacement = array_unique($replacement);
                    $replacement = count($replacement) > 1 ?
                        "[\n\t\t\t" . implode("\n\t\t\t", $replacement) . "\n\t\t]" :
                        $replacement[0];
                }
                $replacement = empty($replacement) ? 'No suggested replacement at this time' : $replacement;
                $replacementString .= "\t\t'$function' => '$replacement'\n";
            }
            $result .= sprintf(
                "Functions '%s' are not secure in %s. \n\tSuggested replacement:\n%s",
                implode(', ', $detectedFunctions),
                $fileFullPath . ':' . $lineNumber,
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
     * Get functions for the given file
     *
     * @param string $fileFullPath
     * @return array
     */
    private function getFunctions($fileFullPath)
    {
        $fileExtension = pathinfo($fileFullPath, PATHINFO_EXTENSION);
        $functions = [];
        if ($fileExtension == 'php') {
            $functions = self::$phpUnsecureFunctions;
        } elseif ($fileExtension == 'js') {
            $functions = self::$jsUnsecureFunctions;
        } elseif ($fileExtension == 'phtml') {
            $functions = array_merge_recursive(self::$phpUnsecureFunctions, self::$jsUnsecureFunctions);
        }
        foreach ($functions as $function => $functionRules) {
            if (in_array($fileFullPath, $functionRules['exclude'])) {
                unset($functions[$function]);
            }
        }
        return $functions;
    }
}
