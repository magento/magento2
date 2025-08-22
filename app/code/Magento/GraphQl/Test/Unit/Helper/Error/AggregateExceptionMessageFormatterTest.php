<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Helper\Error;

use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Phrase;
use Magento\GraphQl\Helper\Error\AggregateExceptionMessageFormatter;
use Magento\GraphQl\Helper\Error\ExceptionMessageFormatterInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AggregateExceptionMessageFormatterTest extends TestCase
{
    /**
     * @var ExceptionMessageFormatterInterface|MockObject
     */
    private ExceptionMessageFormatterInterface $formatter;

    /**
     * @var LocalizedException|LocalizedException&MockObject|MockObject
     */
    private LocalizedException $exception;

    /**
     * @var Phrase|Phrase&MockObject|MockObject
     */
    private Phrase $phrase;

    /**
     * @var Field|Field&MockObject|MockObject
     */
    private Field $field;

    /**
     * @var ContextInterface|ContextInterface&MockObject|MockObject
     */
    private ContextInterface $context;

    /**
     * @var ResolveInfo|ResolveInfo&MockObject|MockObject
     */
    private ResolveInfo $info;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->formatter = $this->createMock(ExceptionMessageFormatterInterface::class);
        $this->exception = $this->createMock(LocalizedException::class);
        $this->phrase = $this->createMock(Phrase::class);
        $this->field = $this->createMock(Field::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->info = $this->createMock(ResolveInfo::class);

        parent::setUp();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGetFormattedUsingFormatter(): void
    {
        $clientAware = $this->createMock(ClientAware::class);
        $this->formatter->expects($this->once())
            ->method('getFormatted')
            ->willReturn($clientAware);
        $messagePrefix = 'prefix';

        $aggregateFormatter = new AggregateExceptionMessageFormatter([$this->formatter]);
        $this->assertSame(
            $clientAware,
            $aggregateFormatter->getFormatted(
                $this->exception,
                $this->phrase,
                $messagePrefix,
                $this->field,
                $this->context,
                $this->info
            )
        );
    }

    /**
     * @return void
     */
    public function testGetFormattedExceptionMessage(): void
    {
        $exceptionCode = 1;
        $exceptionMessage = 'exception message';
        $messagePrefix = 'prefix';
        $this->formatter->expects($this->once())
            ->method('getFormatted')
            ->willReturn(null);
        $paramException = new LocalizedException(__($exceptionMessage), null, $exceptionCode);

        $aggregateFormatter = new AggregateExceptionMessageFormatter([$this->formatter]);
        $exception = $aggregateFormatter->getFormatted(
            $paramException,
            $this->phrase,
            $messagePrefix,
            $this->field,
            $this->context,
            $this->info
        );
        $this->assertInstanceOf(GraphQlInputException::class, $exception);
        $this->assertSame($exceptionCode, $exception->getCode());
        $this->assertSame($messagePrefix . ": " . $exceptionMessage, $exception->getMessage());
    }

    /**
     * @return void
     */
    public function testGetFormattedDefaultMessage(): void
    {
        $exceptionMessage = 'exception message';
        $messagePrefix = '';
        $this->formatter->expects($this->once())
            ->method('getFormatted')
            ->willReturn(null);
        $paramException = new LocalizedException(__($exceptionMessage));

        $this->phrase->expects($this->once())
            ->method('render')
            ->willReturn('prefix default');
        $aggregateFormatter = new AggregateExceptionMessageFormatter([$this->formatter]);
        $exception = $aggregateFormatter->getFormatted(
            $paramException,
            $this->phrase,
            $messagePrefix,
            $this->field,
            $this->context,
            $this->info
        );
        $this->assertInstanceOf(GraphQlInputException::class, $exception);
        $this->assertSame('prefix default', $exception->getMessage());
    }
}
