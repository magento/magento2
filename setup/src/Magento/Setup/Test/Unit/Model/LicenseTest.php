<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\License;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    private $directoryReadMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Filesystem
     */
    private $filesystemMock;

    public function setUp()
    {
        $this->directoryReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
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
