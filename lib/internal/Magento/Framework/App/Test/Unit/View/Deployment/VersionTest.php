<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\View\Deployment;

use Magento\Framework\App\View\Deployment\Version;
use Magento\Framework\Exception\FileSystemException;

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

    public function testGetValueDeveloperMode()
    {
        $this->appStateMock
            ->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->versionStorageMock->expects($this->never())->method($this->anything());
        $this->assertInternalType('integer', $this->object->getValue());
        $this->object->getValue(); // Ensure computation occurs only once and result is cached in memory
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

    /**
     * $param bool $isUnexpectedValueExceptionThrown
     * $param bool $isFileSystemExceptionThrown
     * @dataProvider getValueDefaultModeDataProvider
     */
    public function testGetValueDefaultMode(
        $isUnexpectedValueExceptionThrown,
        $isFileSystemExceptionThrown = null
    ) {
        $versionType = 'integer';
        $this->appStateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_DEFAULT);
        if ($isUnexpectedValueExceptionThrown) {
            $storageException = new \UnexpectedValueException('Does not exist in the storage');
            $this->versionStorageMock
                ->expects($this->once())
                ->method('load')
                ->will($this->throwException($storageException));
            $this->versionStorageMock->expects($this->once())
                ->method('save')
                ->with($this->isType($versionType));
            if ($isFileSystemExceptionThrown) {
                $fileSystemException = new FileSystemException(
                    new \Magento\Framework\Phrase('Can not load static content version')
                );
                $this->versionStorageMock
                    ->expects($this->once())
                    ->method('save')
                    ->will($this->throwException($fileSystemException));
                $this->loggerMock->expects($this->once())
                    ->method('critical')
                    ->with('Can not save static content version.');
            } else {
                $this->loggerMock->expects($this->never())
                    ->method('critical');
            }
        } else {
            $this->versionStorageMock
                ->expects($this->once())
                ->method('load')
                ->willReturn(1475779229);
            $this->loggerMock->expects($this->never())
                ->method('critical');
        }
        $this->assertInternalType($versionType, $this->object->getValue());
        $this->object->getValue();
    }

    /**
     * @return array
     */
    public function getValueDefaultModeDataProvider()
    {
        return [
            [false],
            [true, false],
            [true, true]
        ];
    }

    /**
     * @param bool $isUnexpectedValueExceptionThrown
     * @dataProvider getValueProductionModeDataProvider
     */
    public function testGetValueProductionMode(
        $isUnexpectedValueExceptionThrown
    ) {
        $this->appStateMock
            ->expects($this->once())
            ->method('getMode')
            ->willReturn(\Magento\Framework\App\State::MODE_PRODUCTION);
        if ($isUnexpectedValueExceptionThrown) {
            $storageException = new \UnexpectedValueException('Does not exist in the storage');
            $this->versionStorageMock
                ->expects($this->once())
                ->method('load')
                ->will($this->throwException($storageException));
            $this->loggerMock->expects($this->once())
                ->method('critical')
                ->with('Can not load static content version.');
        } else {
            $this->versionStorageMock
                ->expects($this->once())
                ->method('load')
                ->willReturn(1475779229);
        }
        $this->assertInternalType('integer', $this->object->getValue());
        $this->object->getValue();
    }

    /**
     * @return array
     */
    public function getValueProductionModeDataProvider()
    {
        return [
            [false],
            [true],
        ];
    }
}
