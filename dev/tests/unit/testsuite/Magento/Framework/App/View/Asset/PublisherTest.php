<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

class PublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

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
     * @var Publisher\PublisherInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisherFile;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->publisherFile = $this->getMock(
            'Magento\Framework\App\View\Asset\Publisher\PublisherInterface',
            [],
            [],
            '',
            false
        );
        $this->object = new Publisher($this->filesystem, $this->publisherFile);

        $this->rootDirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->staticDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->staticDirWrite = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->staticDirRead));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValueMap([
                [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirWrite],
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirWrite],
            ]));
    }

    public function testPublishExistsBefore()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(true));
        $this->assertTrue($this->object->publish($this->getAsset()));
    }

    public function testPublish()
    {
        $this->staticDirRead->expects($this->once())
            ->method('isExist')
            ->with('some/file.ext')
            ->will($this->returnValue(false));

        $this->rootDirWrite->expects($this->once())
            ->method('getRelativePath')
            ->with('/root/some/file.ext')
            ->will($this->returnValue('some/file.ext'));
        $this->publisherFile->expects($this->once())
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
