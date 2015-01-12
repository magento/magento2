<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    protected $_filesystem;

    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirReadFactoryMock;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirWriteFactoryMock;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject  */
    protected $_directoryListMock;

    public function setUp()
    {
        $this->_dirReadFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadFactory',
            [],
            [],
            '',
            false
        );
        $this->_directoryListMock = $this->getMock(
            'Magento\Framework\App\Filesystem\DirectoryList',
            [],
            [],
            '',
            false
        );
        $this->_dirWriteFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\WriteFactory',
            [],
            [],
            '',
            false
        );
        $this->_filesystem = new Filesystem(
            $this->_directoryListMock,
            $this->_dirReadFactoryMock,
            $this->_dirWriteFactoryMock
        );
    }

    public function testGetDirectoryRead()
    {
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $dirReadMock */
        $dirReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirReadMock));
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryRead(DirectoryList::ROOT));
    }

    public function testGetDirectoryWrite()
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $dirWriteMock */
        $dirWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->_dirWriteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirWriteMock));
        $this->assertEquals($dirWriteMock, $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT));
    }

    public function testGetUri()
    {
        $this->_directoryListMock->expects($this->once())->method('getUrlPath')->with('code')->willReturn('result');
        $this->assertEquals('result', $this->_filesystem->getUri('code'));
    }
}
