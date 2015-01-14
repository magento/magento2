<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test to ensure that readme file present in specified directories
 */
namespace Magento\Test\Integrity\Readme;

use Magento\Framework\Test\Utility\Files;

class ReadmeTest extends \PHPUnit_Framework_TestCase
{
    const README_FILENAME = 'README.md';

    const BLACKLIST_FILES_PATTERN = '_files/blacklist/*.txt';

    const SCAN_LIST_FILE = '_files/scan_list.txt';

    /** @var array Blacklisted files and directories */
    private $blacklist = [];

    /** @var array */
    private $scanList = [];

    /**
     * @var string Path to project root
     */
    private $root;

    protected function setUp()
    {
        $this->root = Files::init()->getPathToSource();
        $this->blacklist = $this->getBlacklistFromFile();
        $this->scanList = $this->getScanListFromFile();
    }

    public function testReadmeFiles()
    {
        $invoker = new \Magento\Framework\Test\Utility\AggregateInvoker($this);
        $invoker(
        /**
         * @param string $dir
         */
            function ($dir) {
                $file = $dir . DIRECTORY_SEPARATOR . self::README_FILENAME;
                $this->assertFileExists(
                    $file,
                    sprintf('File %s not found in %s', self::README_FILENAME, $dir)
                );
            },
            $this->getDirectories()
        );
    }

    /**
     * @return array
     */
    private function getDirectories()
    {
        $root = $this->root;
        $directories = [];
        foreach ($this->scanList as $pattern) {
            foreach (glob("{$root}/{$pattern}", GLOB_ONLYDIR) as $dir) {
                if (!$this->isInBlacklist($dir)) {
                    $directories[][$dir] = $dir;
                }
            }
        }

        return $directories;
    }

    /**
     * @return array
     */
    private function getBlacklistFromFile()
    {
        $blacklist = [];
        foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . self::BLACKLIST_FILES_PATTERN) as $file) {
            foreach (file($file) as $path) {
                $blacklist[] = $this->root . trim(($path[0] === '/' ? $path : '/' . $path));
            }
        }
        return $blacklist;
    }

    /**
     * @return array
     */
    private function getScanListFromFile()
    {
        $patterns = [];
        $filename = __DIR__ . DIRECTORY_SEPARATOR . self::SCAN_LIST_FILE;
        foreach (file($filename) as $pattern) {
            $patterns[] = trim($pattern);
        }
        return $patterns;
    }

    /**
     * @param $path
     * @return bool
     */
    private function isInBlacklist($path)
    {
        return in_array($path, $this->blacklist);
    }
}
