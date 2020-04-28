<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

    public function setUp(): void
    {
        $this->directoryReadMock = $this->createMock(Read::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadMock));
    }

    public function testGetContents()
    {
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('readFile')
            ->will($this->returnValue('License text'));
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('isFile')
            ->will($this->returnValue(true));

        $license = new License($this->filesystemMock);
        $this->assertSame('License text', $license->getContents());
    }

    public function testGetContentsNoFile()
    {
        $this->directoryReadMock
            ->expects($this->atLeastOnce())
            ->method('isFile')
            ->will($this->returnValue(false));

        $license = new License($this->filesystemMock);
        $this->assertFalse($license->getContents());
    }
}
