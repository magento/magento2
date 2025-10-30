<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Test\Unit\Handler;

use DateTime;
use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Exception as ExceptionHandler;
use Magento\Framework\Logger\Handler\System;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class SystemTest extends TestCase
{
    /**
     * @var System
     */
    private $model;

    /**
     * @var DriverInterface|Mock
     */
    private $filesystemMock;

    /**
     * @var ExceptionHandler|Mock
     */
    private $exceptionHandlerMock;

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->filesystemMock = $this->getMockBuilder(DriverInterface::class)
            ->getMockForAbstractClass();
        $this->exceptionHandlerMock = $this->getMockBuilder(
            ExceptionHandler::class
        )->disableOriginalConstructor()->getMock();

        $this->model = new System(
            $this->filesystemMock,
            $this->exceptionHandlerMock
        );
    }

    public function testWrite()
    {
        $record = $this->getRecord(Level::Warning, 'Test message', []);
        $this->filesystemMock->expects($this->once())
            ->method('getParentDirectory');
        $this->filesystemMock->expects($this->once())
            ->method('isDirectory')
            ->willReturn('true');
        $this->exceptionHandlerMock->expects($this->never())->method('handle');
        $this->model->write($record);
    }

    public function testWriteException()
    {
        $exception = new \Exception('Test exception');
        $record = $this->getRecord(Level::Error, 'Error message', ['exception' => $exception]);
        // Expect the exception handler to be called once with the record
        $this->exceptionHandlerMock->expects($this->once())->method('handle')->with($this->equalTo($record));
        $this->filesystemMock->expects($this->never())
            ->method('getParentDirectory');
        $this->model->write($record);
    }

    /**
     * @param Level $level
     * @param string $message
     * @param array $exception
     * @return LogRecord
     */
    private function getRecord(
        Level $level,
        string $message,
        $exception
    ): LogRecord {
        return new LogRecord(
            new \DateTimeImmutable(),
            'test',
            $level,
            $message,
            $exception
        );
    }
}
