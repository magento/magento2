<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Test\Unit\DB\Logger;

use Magento\Framework\DB\Logger\FileFactory;
use Magento\Framework\DB\Logger\QuietFactory;
use Magento\Framework\DB\Logger\LoggerProxy;
use Magento\Framework\DB\Logger\File;
use Magento\Framework\DB\Logger\Quiet;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LoggerProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Logger\LoggerProxy
     */
    private $loggerProxy;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Test new logger proxy with file alias
     */
    public function testNewWithAliasFile()
    {
        $fileLoggerMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoggerMock->expects($this->once())
            ->method('log');

        $fileLoggerFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $fileLoggerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($fileLoggerMock);

        $quietLoggerMock = $this->getMockBuilder(Quiet::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quietLoggerMock->expects($this->never())
            ->method('log');

        $quietLoggerFactoryMock = $this->getMockBuilder(QuietFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->loggerProxy = $this->objectManager->getObject(
            LoggerProxy::class,
            [
                'fileFactory' => $fileLoggerFactoryMock,
                'quietFactory' => $quietLoggerFactoryMock,
                'loggerAlias' => LoggerProxy::LOGGER_ALIAS_FILE,
            ]
        );

        $this->loggerProxy->log('test');
    }

    /**
     * Test new logger proxy with disabled alias
     */
    public function testNewWithAliasDisabled()
    {
        $fileLoggerMock = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fileLoggerMock->expects($this->never())
            ->method('log');

        $fileLoggerFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $quietLoggerMock = $this->getMockBuilder(Quiet::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quietLoggerMock->expects($this->once())
            ->method('log');

        $quietLoggerFactoryMock = $this->getMockBuilder(QuietFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $quietLoggerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($quietLoggerMock);

        $this->loggerProxy = $this->objectManager->getObject(
            LoggerProxy::class,
            [
                'fileFactory' => $fileLoggerFactoryMock,
                'quietFactory' => $quietLoggerFactoryMock,
                'loggerAlias' => LoggerProxy::LOGGER_ALIAS_DISABLED,
            ]
        );

        $this->loggerProxy->log('test');
    }
}
