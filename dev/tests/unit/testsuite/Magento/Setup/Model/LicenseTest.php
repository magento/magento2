<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

class LicenseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryReadMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    public function setUp()
    {
        $this->directoryReadMock = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\Read')
            ->disableOriginalConstructor()
            ->setMethods(['readFile', 'isFile'])
            ->getMock();
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryRead'])
            ->getMock();
        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->will($this->returnValue($this->directoryReadMock));
    }

    public function testGetContents()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with(License::LICENSE_FILENAME)
            ->will($this->returnValue(file_get_contents(BP . '/' . License::LICENSE_FILENAME)));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isFile')
            ->with(License::LICENSE_FILENAME)
            ->will($this->returnValue(true));

        $license = new License($this->filesystemMock);
        $this->assertSame(file_get_contents(BP . '/' . License::LICENSE_FILENAME), $license->getContents());
    }

    public function testGetContentsNoFile()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isFile')
            ->with(License::LICENSE_FILENAME)
            ->will($this->returnValue(false));

        $license = new License($this->filesystemMock);
        $this->assertFalse($license->getContents());
    }
}
