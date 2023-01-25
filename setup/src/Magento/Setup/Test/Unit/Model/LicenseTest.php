<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Setup\Model\License;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LicenseTest extends TestCase
{
    /**
     * @var MockObject|Read
     */
    private $directoryReadMock;

    /**
     * @var MockObject|Filesystem
     */
    private $filesystemMock;

    protected function setUp(): void
    {
        $this->directoryReadMock = $this->createMock(Read::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
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
