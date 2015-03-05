<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test to ensure that readme file present in specified directories
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use \Magento\Framework\App\Bootstrap;

class TestPlacementTest extends \PHPUnit_Framework_TestCase
{
    const SCAN_LIST_FILE = '_files/placement_test/scan_list.txt';

    /** @var array */
    private $scanList = [];

    /**
     * @var string Path to project root
     */
    private $root;

    protected function setUp()
    {
        $this->root = Files::init()->getPathToSource();
        $this->scanList = $this->getScanListFromFile();
    }

    public function testReadmeFiles()
    {
        $objectManager = Bootstrap::create(BP, $_SERVER)->getObjectManager();
        /** @var \Magento\Framework\Data\Collection\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\Framework\Data\Collection\Filesystem');
        $filesystem->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(true)
            ->setFilesFilter('/\Test.(php)$/i');

        foreach ($this->scanList as $dir) {
            $filesystem->addTargetDir($this->root . '/' . $dir);
        }

        $files = $filesystem->load()->toArray();
        $fileList = '';
        foreach ($files['items'] as $file) {
            $fileList.= "\n" . $file['filename'];
        }
        $fileList.= "\n";
        $this->assertEquals(
            0,
            $files['totalRecords'],
            "Unit tests has been found in directories: \n" . implode("\n", $this->scanList)
            . "\nUnit test list:" . $fileList
        );
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
}
