<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    /** @var Filesystem */
    protected $_filesystem;

    /** @var ReadFactory|MockObject */
    protected $_dirReadFactoryMock;

    /** @var WriteFactory|MockObject */
    protected $_dirWriteFactoryMock;

    /** @var DirectoryList|MockObject  */
    protected $_directoryListMock;

    protected function setUp(): void
    {
        $this->_dirReadFactoryMock = $this->createMock(ReadFactory::class);
        $this->_directoryListMock = $this->createMock(DirectoryList::class);
        $this->_dirWriteFactoryMock = $this->createMock(WriteFactory::class);
        $this->_filesystem = new Filesystem(
            $this->_directoryListMock,
            $this->_dirReadFactoryMock,
            $this->_dirWriteFactoryMock
        );
    }

    public function testGetDirectoryRead()
    {
        /** @var ReadInterface $dirReadMock */
        $dirReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->willReturn($dirReadMock);
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryRead(DirectoryList::ROOT));
    }

    public function testGetDirectoryReadByPath()
    {
        /** @var ReadInterface $dirReadMock */
        $dirReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->willReturn($dirReadMock);
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryReadByPath('path/to/some/file'));
    }

    public function testGetDirectoryWrite()
    {
        /** @var WriteInterface $dirWriteMock */
        $dirWriteMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->_dirWriteFactoryMock->expects($this->once())->method('create')->willReturn($dirWriteMock);
        $this->assertEquals($dirWriteMock, $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT));
    }

    public function testGetUri()
    {
        $this->_directoryListMock->expects($this->once())->method('getUrlPath')->with('code')->willReturn('result');
        $this->assertEquals('result', $this->_filesystem->getUri('code'));
    }
}
