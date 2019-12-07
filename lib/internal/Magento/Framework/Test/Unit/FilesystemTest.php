<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

use \Magento\Framework\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;

class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    /** @var Filesystem */
    protected $_filesystem;

    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirReadFactoryMock;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirWriteFactoryMock;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject  */
    protected $_directoryListMock;

    protected function setUp()
    {
        $this->_dirReadFactoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $this->_directoryListMock = $this->createMock(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $this->_dirWriteFactoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteFactory::class);
        $this->_filesystem = new Filesystem(
            $this->_directoryListMock,
            $this->_dirReadFactoryMock,
            $this->_dirWriteFactoryMock
        );
    }

    public function testGetDirectoryRead()
    {
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $dirReadMock */
        $dirReadMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirReadMock));
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryRead(DirectoryList::ROOT));
    }

    public function testGetDirectoryReadByPath()
    {
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $dirReadMock */
        $dirReadMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirReadMock));
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryReadByPath('path/to/some/file'));
    }

    public function testGetDirectoryWrite()
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $dirWriteMock */
        $dirWriteMock = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->_dirWriteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirWriteMock));
        $this->assertEquals($dirWriteMock, $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT));
    }

    public function testGetUri()
    {
        $this->_directoryListMock->expects($this->once())->method('getUrlPath')->with('code')->willReturn('result');
        $this->assertEquals('result', $this->_filesystem->getUri('code'));
    }
}
