<?php
/**
 * Test for \Magento\Framework\Filesystem
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList as AppDirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class FilesystemTest
 * Test for Magento\Framework\Filesystem class
 *
 */
class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->filesystem = Bootstrap::getObjectManager()->create(\Magento\Framework\Filesystem::class);
    }

    /**
     * Test getDirectoryRead method return valid instance
     */
    public function testGetDirectoryReadInstance()
    {
        $dir = $this->filesystem->getDirectoryRead(AppDirectoryList::VAR_DIR);
        $this->assertInstanceOf(\Magento\Framework\Filesystem\Directory\Read::class, $dir);
    }

    /**
     * Test getDirectoryWrite method return valid instance
     */
    public function testGetDirectoryWriteInstance()
    {
        $dir = $this->filesystem->getDirectoryWrite(AppDirectoryList::VAR_DIR);
        $this->assertInstanceOf(\Magento\Framework\Filesystem\Directory\Write::class, $dir);
    }

    /**
     * Test getUri returns right uri
     */
    public function testGetUri()
    {
        $this->assertStringContainsString(
            'media',
            $this->filesystem->getDirectoryRead(AppDirectoryList::MEDIA)->getAbsolutePath()
        );
    }
}
