<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Legacy;

use Magento\Framework\App\Utility\Files;

/**
 * Tests to detect unsecure functions usage
 */
class UnsecureFunctionsUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * File extensions pattern to search for
     *
     * @var string
     */
    private $fileExtensions = '/\.(php|phtml|js)$/';

    /**
     * Php unsecure functions to detect
     *
     * @var array
     */
    private $phpUnsecureFunctions = [
        'unserialize',
        'serialize',
        'eval',
        'md5',
        'srand',
        'mt_srand'
    ];

    /**
     * JS unsecure functions to detect
     *
     * @var array
     */
    private $jsUnsecureFunctions = [];

    /**
     * Detect unsecure functions usage for changed files in whitelist with the exception of blacklist
     *
     * @return void
     */
    public function testUnsecureFunctionsUsage()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
        $invoker(
            function ($fileName) {
                $result = '';
                $errorMessage = 'The following functions are non secure and should be avoided: '
                    . implode(', ', $this->phpUnsecureFunctions)
                    . ' for PHP';
                if (!empty($this->jsUnsecureFunctions)) {
                    $errorMessage .= ', and '
                        . implode(', ', $this->jsUnsecureFunctions)
                        . ' for JavaScript';
                }
                $errorMessage .= ".\n";
                $regexp = $this->getRegexpByFileExtension(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($regexp) {
                    $matches = preg_grep(
                        $regexp,
                        file($fileName)
                    );
                    if (!empty($matches)) {
                        foreach (array_keys($matches) as $line) {
                            $result .= $fileName . ':' . ($line + 1) . "\n";
                        }
                    }
                    $this->assertEmpty($result, $errorMessage . $result);
                }
            },
            $this->unsecureFunctionsUsageDataProvider()
        );
    }

    /**
     * Data provider for test
     *
     * @return array
     */
    public function unsecureFunctionsUsageDataProvider()
    {
        $fileExtensions = $this->fileExtensions;
        $directoriesToScan = Files::init()->readLists(__DIR__ . '/_files/security/whitelist.txt');
        $blackListFiles = include __DIR__ . '/_files/security/blacklist.php';

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
            function ($path) use ($directoriesToScan, $fileExtensions, $blackListFiles) {
                if (!file_exists($path[0])) {
                    return false;
                }
                $path = realpath($path[0]);
                foreach ($directoriesToScan as $directory) {
                    $directory = realpath($directory);
                    if (strpos($path, $directory) === 0) {
                        if (preg_match($fileExtensions, $path)) {
                            foreach ($blackListFiles as $blackListFile) {
                                $blackListFile = preg_quote($blackListFile, '#');
                                if (preg_match('#' . $blackListFile . '#', $path)) {
                                    return false;
                                }
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
     * Get regular expression by file extension
     *
     * @param string $fileExtension
     * @return string|bool
     */
    private function getRegexpByFileExtension($fileExtension)
    {
        $regexp = false;
        if ($fileExtension == 'php') {
            $regexp = $this->prepareRegexp($this->phpUnsecureFunctions);
        } elseif ($fileExtension == 'js') {
            $regexp = $this->prepareRegexp($this->jsUnsecureFunctions);
        } elseif ($fileExtension == 'phtml') {
            $regexp = $this->prepareRegexp($this->phpUnsecureFunctions + $this->jsUnsecureFunctions);
        }
        return $regexp;
    }

    /**
     * Prepare regular expression for unsecure function names
     *
     * @param array $functions
     * @return string
     */
    private function prepareRegexp(array $functions)
    {
        if (empty($functions)) {
            return '';
        }
        return '/(?<!function |[^\s])\b(' . join('|', $functions) . ')\s*\(/i';
    }
}
