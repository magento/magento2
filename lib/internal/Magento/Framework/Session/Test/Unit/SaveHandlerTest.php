<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\Session\SaveHandler
 */

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandler;
use Magento\Framework\Session\SaveHandlerFactory;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionMaxSizeConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $helper;

    /**
     * @var SaveHandler
     */
    protected $saveHandler;

    /**
     * @var SaveHandlerFactory|MockObject
     */
    protected $saveHandlerFactoryMock;

    /**
     * @var SaveHandlerInterface|MockObject
     */
    protected $saveHandlerAdapterMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var SessionMaxSizeConfig|MockObject
     */
    protected $sessionMaxSizeConfigMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sessionMaxSizeConfigMock = $this->getMockBuilder(SaveHandlerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionMaxSize'])
            ->getMock();

        $this->saveHandlerAdapterMock = $this->getMockBuilder(SaveHandlerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['write'])
            ->getMockForAbstractClass();

        $this->saveHandlerAdapterMock->expects($this->any())
            ->method('write')
            ->willReturn(true);

        $this->saveHandlerFactoryMock = $this->getMockBuilder(SaveHandlerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->saveHandlerFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->saveHandlerAdapterMock);

        $this->helper = new ObjectManager($this);
        $this->saveHandler = $this->helper->getObject(
            SaveHandler::class,
            [
                'saveHandlerFactory' => $this->saveHandlerFactoryMock,
                'sessionConfig' => $this->configMock,
                'sessionMaxSizeConfig' => $this->sessionMaxSizeConfigMock,
            ]
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

    public function testWriteMoreThanSessionMaxSize()
    {
        $this->sessionMaxSizeConfigMock->expects($this->once())
            ->method('getSessionMaxSize')
            ->willReturn(1);

        $this->saveHandlerAdapterMock->expects($this->once())
            ->method('read')
            ->with('test_session_id');

        $this->assertTrue($this->saveHandler->write("test_session_id", "testdata"));
    }
}
