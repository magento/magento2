<?php
/**
 * Test for \Magento\Framework\Filesystem
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->filesystem = Bootstrap::getObjectManager()->create('Magento\Framework\Filesystem');
    }

    /**
     * Test getDirectoryRead method return valid instance
     */
    public function testGetDirectoryReadInstance()
    {
        $dir = $this->filesystem->getDirectoryRead(AppDirectoryList::VAR_DIR);
        $this->assertInstanceOf('\Magento\Framework\Filesystem\Directory\Read', $dir);
    }

    /**
     * Test getDirectoryWrite method return valid instance
     */
    public function testGetDirectoryWriteInstance()
    {
        $dir = $this->filesystem->getDirectoryWrite(AppDirectoryList::VAR_DIR);
        $this->assertInstanceOf('\Magento\Framework\Filesystem\Directory\Write', $dir);
    }

    /**
     * Test getUri returns right uri
     */
    public function testGetUri()
    {
        $this->assertContains('media', $this->filesystem->getDirectoryRead(AppDirectoryList::MEDIA)->getAbsolutePath());
    }
}
