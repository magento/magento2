<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandler;
use Magento\Framework\Session\SaveHandlerFactory;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionMaxSizeConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var SaveHandlerFactory|MockObject
     */
    private $saveHandlerFactoryMock;

    /**
     * @var SaveHandlerInterface|MockObject
     */
    private $saveHandlerAdapterMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var SessionMaxSizeConfig|MockObject
     */
    private $sessionMaxSizeConfigMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    /**
     * @var AppState|MockObject
     */
    private $appStateMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->sessionMaxSizeConfigMock = $this->createMock(SessionMaxSizeConfig::class);
        $this->saveHandlerAdapterMock = $this->createMock(SaveHandlerInterface::class);
        $this->saveHandlerAdapterMock->expects($this->any())
            ->method('write')
            ->willReturn(true);
        $this->saveHandlerFactoryMock = $this->createMock(SaveHandlerFactory::class);
        $this->saveHandlerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->saveHandlerAdapterMock);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->appStateMock = $this->createMock(AppState::class);

        $this->saveHandler = new SaveHandler(
            $this->saveHandlerFactoryMock,
            $this->configMock,
            $loggerMock,
            $this->sessionMaxSizeConfigMock,
            SaveHandlerInterface::DEFAULT_HANDLER,
            $this->messageManagerMock,
            $this->appStateMock
        );
    }

    public function testWriteSessionMaxSizeValid()
    {
        $this->sessionMaxSizeConfigMock->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(9);

        $this->saveHandlerAdapterMock->expects($this->never())
            ->method('read');

        $this->assertTrue($this->saveHandler->write("test_session_id", "testdata"));
    }

    public function testWriteSessionMaxSizeNull()
    {
        $this->sessionMaxSizeConfigMock->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(null);

        $this->saveHandlerAdapterMock->expects($this->never())
            ->method('read');

        $this->assertTrue($this->saveHandler->write("test_session_id", "testdata"));
    }

    public function testWriteMoreThanSessionMaxSize(): void
    {
        $this->sessionMaxSizeConfigMock
            ->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(1);

        $this->saveHandlerAdapterMock
            ->expects($this->never())
            ->method('read');

        $this->assertTrue($this->saveHandler->write("test_session_id", "testdata"));
    }

    public function testReadMoreThanSessionMaxSize(): void
    {
        $this->sessionMaxSizeConfigMock
            ->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(1);

        $this->saveHandlerAdapterMock
            ->expects($this->once())
            ->method('read')
            ->with('test_session_id')
            ->willReturn('test_session_data');

        $this->appStateMock->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn(AppArea::AREA_FRONTEND);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->assertEmpty($this->saveHandler->read('test_session_id'));
    }

    public function testReadSessionMaxZero(): void
    {
        $this->sessionMaxSizeConfigMock
            ->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(0);

        $this->saveHandlerAdapterMock
            ->expects($this->once())
            ->method('read')
            ->with('test_session_id')
            ->willReturn('test_session_data');

        $this->appStateMock->expects($this->atLeastOnce())
            ->method('getAreaCode')
            ->willReturn(AppArea::AREA_FRONTEND);
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->assertEmpty($this->saveHandler->read('test_session_id'));
    }

    public function testReadMoreThanSessionMaxSizeAdmin(): void
    {
        $this->sessionMaxSizeConfigMock->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(1);

        $this->saveHandlerAdapterMock->expects($this->once())
            ->method('read')
            ->with('test_session_id')
            ->willReturn('test_session_data');

        $this->appStateMock->expects($this->once())
            ->method('getAreaCode')
            ->willReturn(AppArea::AREA_ADMINHTML);

        $this->assertEquals('test_session_data', $this->saveHandler->read('test_session_id'));
    }
}
