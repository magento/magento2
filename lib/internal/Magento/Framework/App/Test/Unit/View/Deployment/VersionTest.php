<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\View\Deployment;

use Magento\Framework\App\View\Deployment\Version;

/**
 * Class VersionTest
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Version
     */
    private $object;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appStateMock;

    /**
     * @var \Magento\Framework\App\View\Deployment\Version\StorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $versionStorageMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->appStateMock = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);
        $this->versionStorageMock = $this->getMock(
            \Magento\Framework\App\View\Deployment\Version\StorageInterface::class
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->object = new Version($this->appStateMock, $this->versionStorageMock);
        $objectManager->setBackwardCompatibleProperty($this->object, 'logger', $this->loggerMock);
    }

    /**
     * @param string $appMode
     * @dataProvider getValueFromStorageDataProvider
     */
    public function testGetValueFromStorage($appMode)
    {
        $this->appStateMock
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue($appMode));
        $this->versionStorageMock->expects($this->once())->method('load')->will($this->returnValue('123'));
        $this->versionStorageMock->expects($this->never())->method('save');
        $this->assertEquals('123', $this->object->getValue());
        $this->object->getValue(); // Ensure caching in memory
    }

    public function getValueFromStorageDataProvider()
    {
        return [
            'default mode'      => [\Magento\Framework\App\State::MODE_DEFAULT],
            'production mode'   => [\Magento\Framework\App\State::MODE_PRODUCTION],
            'arbitrary mode'    => ['test'],
        ];
    }

    public function testGetValueInNonProductionMode()
    {
        $version = 123123123123;
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn($version);

        $this->assertEquals($version, $this->object->getValue());
        $this->object->getValue();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetValueWithProductionModeAndException()
    {
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with('Can not load static content version.');

        $this->object->getValue();
    }

    public function testGetValueWithProductionMode()
    {
        $this->versionStorageMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEFAULT);
        $this->versionStorageMock->expects($this->once())
            ->method('save');

        $this->assertNotNull($this->object->getValue());
    }
}
