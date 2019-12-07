<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test to ensure that readme file present in specified directories
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;
use \Magento\Framework\App\Bootstrap;

class TestPlacementTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    private $scanList = ['dev/tests/unit/testsuite/Magento'];

    /**
     * @var string Path to project root
     */
    private $root;

    protected function setUp()
    {
        $this->root = BP;
    }

    public function testUnitTestFilesPlacement()
    {
        $objectManager = Bootstrap::create(BP, $_SERVER)->getObjectManager();
        /** @var \Magento\Framework\Data\Collection\Filesystem $filesystem */
        $filesystem = $objectManager->get(\Magento\Framework\Data\Collection\Filesystem::class);
        $filesystem->setCollectDirs(false)
            ->setCollectFiles(true)
            ->setCollectRecursively(true);

        $targetsExist = false;
        foreach ($this->scanList as $dir) {
            if (realpath($this->root . DIRECTORY_SEPARATOR . $dir)) {
                $filesystem->addTargetDir($this->root . DIRECTORY_SEPARATOR . $dir);
                $targetsExist = true;
            }
        }

        if ($targetsExist) {
            $files = $filesystem->load()->toArray();
            $fileList = [];
            foreach ($files['items'] as $file) {
                $fileList[] = $file['filename'];
            }

            $this->assertEquals(
                0,
                $files['totalRecords'],
                "The following files have been found in obsolete test directories: \n"
                . implode("\n", $fileList)
            );
        }
    }
}
