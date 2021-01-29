<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\License;

class LicenseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    private $directoryReadMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    protected function setUp(): void
    {
        $this->directoryReadMock = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->directoryReadMock);
    }

    public function testGetContents()
    {
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('readFile')
            ->willReturn('License text');
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('isFile')
            ->willReturn(true);

        $license = new License($this->filesystemMock);
        $this->assertSame('License text', $license->getContents());
    }

    public function testGetContentsNoFile()
    {
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('isFile')
            ->willReturn(false);

        $license = new License($this->filesystemMock);
        $this->assertFalse($license->getContents());
    }
}
