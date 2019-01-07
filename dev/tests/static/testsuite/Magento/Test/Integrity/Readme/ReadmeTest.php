<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test to ensure that readme file present in specified directories
 */
namespace Magento\Test\Integrity\Readme;

use Magento\Framework\App\Utility\Files;

class ReadmeTest extends \PHPUnit\Framework\TestCase
{
    const README_FILENAME = 'README.md';

    const BLACKLIST_FILES_PATTERN = '_files/blacklist/*.txt';

    const SCAN_LIST_FILE = '_files/scan_list.txt';

    /** @var array Blacklisted files and directories */
    private $blacklist = [];

    /** @var array */
    private $scanList = [];

    protected function setUp()
    {
        $this->blacklist = $this->getPaths(__DIR__ . '/' . self::BLACKLIST_FILES_PATTERN);
        $this->scanList = $this->getPaths(__DIR__ . '/' . self::SCAN_LIST_FILE);
    }

    public function testReadmeFiles()
    {
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
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
        $directories = [];
        foreach ($this->scanList as $dir) {
            if (!$this->isInBlacklist($dir)) {
                $directories[][$dir] = $dir;
            }
        }

        return $directories;
    }

    /**
     * @param $path
     * @return bool
     */
    private function isInBlacklist($path)
    {
        return in_array($path, $this->blacklist);
    }

    /**
     * Get paths basing on the file with patterns
     *
     * @param string $patternsFile
     * @return array
     */
    private function getPaths($patternsFile)
    {
        $result = [];
        $files = Files::init()->readLists($patternsFile);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $result[] = rtrim($file, '/');
            }
        }
        return $result;
    }
}
