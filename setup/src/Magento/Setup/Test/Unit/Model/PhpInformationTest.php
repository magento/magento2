<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PhpInformation;

class PhpInformationTest extends \PHPUnit_Framework_TestCase
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
            ->will($this->returnValue('{"platform":{"php":"~a.b.c|~a.e.f"}}'));

        $phpInfo = new PhpInformation($this->filesystemMock);
        $this->assertEquals("~a.b.c|~a.e.f", $phpInfo->getRequiredPhpVersion());
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
