<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Minified;

use Magento\Framework\App\Filesystem\DirectoryList;

class AbstractAssetTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_asset;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_staticViewDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rootDir;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseUrl;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->_logger = $this->getMock('\Psr\Log\LoggerInterface', [], [], '', false);
        $this->_baseUrl = $this->getMock('\Magento\Framework\Url', [], [], '', false);
        $this->_staticViewDir = $this->getMockForAbstractClass(
            '\Magento\Framework\Filesystem\Directory\WriteInterface'
        );
        $this->_rootDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->_filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap([
                [DirectoryList::STATIC_VIEW, $this->_staticViewDir],
                [DirectoryList::ROOT, $this->_rootDir],
            ]));
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->_staticViewDir));
        $this->_adapter = $this->getMockForAbstractClass('Magento\Framework\Code\Minifier\AdapterInterface');
    }

    protected function prepareAttemptToMinifyMock($fileExists, $rootDirExpectations = true)
    {
        $this->_asset->expects($this->atLeastOnce())->method('getPath')->will($this->returnValue('test/admin.js'));
        $this->_asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->will($this->returnValue('/foo/bar/test/admin.js'));
        if ($rootDirExpectations) {
            $this->_rootDir->expects($this->once())
                ->method('getRelativePath')
                ->with('/foo/bar/test/admin.min.js')
                ->will($this->returnValue('test/admin.min.js'));
            $this->_rootDir->expects($this->once())
                ->method('isExist')
                ->with('test/admin.min.js')
                ->will($this->returnValue(false));
        }
        $this->_baseUrl->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->_staticViewDir->expects($this->once())->method('isExist')->will($this->returnValue($fileExists));
    }
}
