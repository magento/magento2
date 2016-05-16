<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\View\Asset;

use \Magento\Framework\App\View\Asset\Publisher;
use \Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

class PublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirRead;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirWrite;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirRead;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirWrite;

    /**
     * @var \Magento\Framework\App\View\Asset\Publisher
     */
    private $object;

    /**
     * @var MaterializationStrategy\Factory |\PHPUnit_Framework_MockObject_MockObject
     */
    private $materializationStrategyFactory;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->materializationStrategyFactory = $this->getMock(
            'Magento\Framework\App\View\Asset\MaterializationStrategy\Factory',
            [],
            [],
            '',
            false
        );

        $this->object = new Publisher($this->filesystem, $this->materializationStrategyFactory);

        $this->rootDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->rootDirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->staticDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->staticDirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap([
                [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirRead],
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirRead],
            ]));

        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap([
                [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirWrite],
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirWrite],
            ]));
    }

    /**
     * This test is supposed to fail because only non-existant dummy files are used
     */
    public function testIsFilesEqualWithDummyFiles()
    {
        $this->assertFalse($this->object->isFilesEqual('some/file.ext', 'some/file.ext'));
    }

    /**
     * This test is supposed to return true because md5_file() will return empty strings (and a suppressed PHP error)
     */
    public function testPublishExistsBefore()
    {
        $this->rootDirRead->expects($this->any())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(true));

        $this->rootDirRead->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnValue('some/file.ext'));

        $this->staticDirRead->expects($this->any())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(true));

        $this->staticDirRead->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnValue('some/file.ext'));

        // Use @ to suppress md5_file() on non-existant files
        $this->assertTrue(@$this->object->publish($this->getAsset()));
    }

    /**
     * Pretend we are publishing a file
     */
    public function testPublish()
    {
        $this->staticDirRead->expects($this->any())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(false));

        $materializationStrategy = $this->getMock(
            'Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface',
            [],
            [],
            '',
            false
        );

        $this->rootDirWrite->expects($this->any())
            ->method('getRelativePath')
            ->with('/root/some/file.ext')
            ->will($this->returnValue('some/file.ext'));

        $this->materializationStrategyFactory->expects($this->once())
            ->method('create')
            ->with($this->getAsset())
            ->will($this->returnValue($materializationStrategy));

        $materializationStrategy->expects($this->once())
            ->method('publishFile')
            ->with($this->rootDirWrite, $this->staticDirWrite, 'some/file.ext', 'some/file.ext')
            ->will($this->returnValue(true));

        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    /**
     * Create an asset mock
     *
     * @return \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAsset()
    {
        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $asset->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('some/file.ext'));
        $asset->expects($this->any())
            ->method('getSourceFile')
            ->will($this->returnValue('/root/some/file.ext'));
        return $asset;
    }
}
