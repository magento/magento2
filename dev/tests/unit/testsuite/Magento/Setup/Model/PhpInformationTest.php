<?php
/**
 * @copyright Copyright (c) 2014 X.commerce', Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

class PhpInformationTest extends \PHPUnit_Framework_TestCase
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
            ->setMethods(['isExist', 'readFile'])
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

    public function testGetRequiredPhpVersion()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with('composer.lock')
            ->will($this->returnValue('{"platform":{"php":"~5.4.11|~5.5.0"}}'));

        $phpInfo = new PhpInformation($this->filesystemMock);
        $this->assertEquals("~5.4.11|~5.5.0", $phpInfo->getRequiredPhpVersion());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot read 'composer.lock' file
     */
    public function testGetRequiredPhpVersionExceptionNoComposerLock()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(false));

        $phpInfo = new PhpInformation($this->filesystemMock);
        $phpInfo->getRequiredPhpVersion();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing key 'platform=>php' in 'composer.lock' file
     */
    public function testGetRequiredPhpVersionExceptionMissingKey()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with('composer.lock')
            ->will($this->returnValue('{}'));

        $phpInfo = new PhpInformation($this->filesystemMock);
        $phpInfo->getRequiredPhpVersion();
    }

    public function testGetRequired()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with('composer.lock')
            ->will($this->returnValue(
                '{"platform-dev":{"ext-e":"*", "f":"*"}, ' .
                '"packages":[{"name":"a","require":{"c":"*"}}, {"name":"b","require":{"ext-d":"*"}}]}'
            ));
        $phpInfo = new PhpInformation($this->filesystemMock);
        $this->assertEquals(['e', 'd'], $phpInfo->getRequired());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot read 'composer.lock' file
     */
    public function testGetRequiredNoComposerLock()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(false));

        $phpInfo = new PhpInformation($this->filesystemMock);
        $phpInfo->getRequired();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing key 'platform-dev' in 'composer.lock' file
     */
    public function testGetRequiredExceptionMissingPlatformDev()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with('composer.lock')
            ->will(
                $this->returnValue(
                    '{"packages":[{"name":"a","require":{"c":"*"}}, {"name":"b","require":{"ext-d":"*"}}]}'
                )
            );
        $phpInfo = new PhpInformation($this->filesystemMock);
        $phpInfo->getRequired();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Missing key 'packages' in 'composer.lock' file
     */
    public function testGetRequiredExceptionMissingPackages()
    {
        $this->directoryReadMock
            ->expects($this->once())
            ->method('isExist')
            ->will($this->returnValue(true));
        $this->directoryReadMock
            ->expects($this->once())
            ->method('readFile')
            ->with('composer.lock')
            ->will($this->returnValue('{"platform-dev":{"ext-e":"*", "f":"*"}}'));
        $phpInfo = new PhpInformation($this->filesystemMock);
        $phpInfo->getRequired();
    }
}
