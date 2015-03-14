<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\Minified;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;

class AbstractAssetTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $asset;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $staticViewDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDir;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $baseUrl;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    protected function setUp()
    {
        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->logger = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $this->baseUrl = $this->getMock('\Magento\Framework\Url', [], [], '', false);
        $this->staticViewDir = $this->getMockForAbstractClass(
            '\Magento\Framework\Filesystem\Directory\WriteInterface'
        );
        $this->rootDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap([
                [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticViewDir],
                [DirectoryList::ROOT, DriverPool::FILE, $this->rootDir],
            ]));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->staticViewDir));
        $this->adapter = $this->getMockForAbstractClass('Magento\Framework\Code\Minifier\AdapterInterface');
    }

    protected function prepareAttemptToMinifyMock($fileExists, $rootDirExpectations = true, $originalExists = true)
    {
        $this->asset->expects($this->atLeastOnce())->method('getPath')->will($this->returnValue('test/admin.js'));
        $this->asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->will($this->returnValue('/foo/bar/test/admin.js'));
        if ($rootDirExpectations) {
            $this->rootDir->expects($this->once())
                ->method('getRelativePath')
                ->with('/foo/bar/test/admin.min.js')
                ->will($this->returnValue('test/admin.min.js'));
            $this->rootDir->expects($this->once())
                ->method('isExist')
                ->with('test/admin.min.js')
                ->will($this->returnValue(false));
        }
        $this->baseUrl->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->staticViewDir
            ->expects($this->exactly(2-intval($originalExists)))
            ->method('isExist')
            ->will($this->returnValue($fileExists));
    }
}
