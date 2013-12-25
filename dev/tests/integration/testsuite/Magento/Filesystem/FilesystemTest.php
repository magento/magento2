<?php
/**
 * Test for \Magento\Filesystem
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class FilesystemTest
 * Test for Magento\Filesystem class
 *
 * @package Magento
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Filesystem
     */
    protected $filesystem;

    protected function setUp()
    {
        $this->filesystem = Bootstrap::getObjectManager()->create('Magento\Filesystem');
    }

    /**
     * Test getDirectoryRead method return valid instance
     */
    public function testGetDirectoryReadInstance()
    {
        $dir = $this->filesystem->getDirectoryRead(\Magento\Filesystem::VAR_DIR);
        $this->assertInstanceOf('\Magento\Filesystem\Directory\Read', $dir);
    }

    /**
     * Test getDirectoryWrite method return valid instance
     */
    public function testGetDirectoryWriteInstance()
    {
        $dir = $this->filesystem->getDirectoryWrite(\Magento\Filesystem::VAR_DIR);
        $this->assertInstanceOf('\Magento\Filesystem\Directory\Write', $dir);
    }

    /**
     * Test getDirectoryWrite throws exception on trying to get directory with write access
     *
     * @expectedException \Magento\Filesystem\FilesystemException
     */
    public function testGetDirectoryWriteException()
    {
        $this->filesystem->getDirectoryWrite(\Magento\Filesystem::THEMES);
    }

    /**
     * Test getPath returns right path
     */
    public function testGetPath()
    {
        $this->assertContains('design', $this->filesystem->getPath(\Magento\Filesystem::THEMES));
    }

    /**
     * Test getUri returns right uri
     */
    public function testGetUri()
    {
        $this->assertContains('media', $this->filesystem->getPath(\Magento\Filesystem::MEDIA));
    }
}
