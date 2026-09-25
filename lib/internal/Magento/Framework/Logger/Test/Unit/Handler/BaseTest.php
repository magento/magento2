<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Test\Unit\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @var Base|MockObject
     */
    private $model;

    /**
     * @var \ReflectionMethod
     */
    private $sanitizeMethod;

    protected function setUp(): void
    {
        $driverMock = $this->createMock(DriverInterface::class);
        $this->model = new Base($driverMock);

        $class = new \ReflectionClass($this->model);
        $this->sanitizeMethod = $class->getMethod('sanitizeFileName');
    }

    public function testSanitizeEmpty()
    {
        $this->assertEquals('', $this->sanitizeMethod->invokeArgs($this->model, ['']));
    }

    public function testSanitizeSimpleFilename()
    {
        $this->assertEquals('custom.log', $this->sanitizeMethod->invokeArgs($this->model, ['custom.log']));
    }

    public function testSanitizeLeadingSlashFilename()
    {
        $this->assertEquals(
            'customfolder/custom.log',
            $this->sanitizeMethod->invokeArgs($this->model, ['/customfolder/custom.log'])
        );
    }

    public function testSanitizeParentLevelFolder()
    {
        $this->assertEquals(
            'var/hack/custom.log',
            $this->sanitizeMethod->invokeArgs($this->model, ['../../../var/hack/custom.log'])
        );
    }

    /**
     * A Throwable reported through the PSR-3 reserved context key must keep its stack trace.
     */
    public function testDefaultFormatterIncludesStackTraces(): void
    {
        $formatted = $this->model->getFormatter()->format($this->createRecordWithException());

        $this->assertStringContainsString('[stacktrace]', $formatted);
        $this->assertStringContainsString(__FUNCTION__, $formatted);
    }

    public function testDefaultFormatterKeepsMessageAndContext(): void
    {
        $formatted = $this->model->getFormatter()->format($this->createRecordWithException());

        $this->assertStringContainsString('Something failed while processing {orderId}', $formatted);
        $this->assertStringContainsString('"orderId":1234', $formatted);
    }

    public function testInjectedFormatterIsUsed(): void
    {
        $formatter = $this->createMock(FormatterInterface::class);
        $handler = new Base($this->createMock(DriverInterface::class), null, null, $formatter);

        $this->assertSame($formatter, $handler->getFormatter());
    }

    private function createRecordWithException(): LogRecord
    {
        return new LogRecord(
            new \DateTimeImmutable('2026-01-01 00:00:00'),
            'main',
            Level::Critical,
            'Something failed while processing {orderId}',
            ['orderId' => 1234, 'exception' => new \RuntimeException('Something failed')]
        );
    }
}
