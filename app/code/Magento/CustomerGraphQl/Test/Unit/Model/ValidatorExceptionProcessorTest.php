<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model;

use Magento\CustomerGraphQl\Model\ValidatorExceptionProcessor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\Exception as ValidatorException;
use PHPUnit\Framework\TestCase;

class ValidatorExceptionProcessorTest extends TestCase
{
    /**
     * @var ValidatorExceptionProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        $this->processor = new ValidatorExceptionProcessor();
    }

    public function testProcessValidatorExceptionForGraphQlWithMessages(): void
    {
        $errorMessage1 = $this->createMock(AbstractMessage::class);
        $errorMessage1->expects($this->once())
            ->method('getText')
            ->willReturn('First Name is not valid!');

        $errorMessage2 = $this->createMock(AbstractMessage::class);
        $errorMessage2->expects($this->once())
            ->method('getText')
            ->willReturn('Last Name is not valid!');

        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage1, $errorMessage2]);

        $result = $this->processor->processValidatorExceptionForGraphQl($validatorException, ' ');

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($validatorException, $result->getPrevious());
    }

    public function testProcessValidatorExceptionForGraphQlWithEmptyMessages(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Combined error message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $result = $this->processor->processValidatorExceptionForGraphQl($validatorException);

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($validatorException, $result->getPrevious());
    }

    public function testProcessValidatorExceptionForGraphQlWithStringMessages(): void
    {
        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn(['First Name is not valid!', 'Last Name is not valid!']);

        $result = $this->processor->processValidatorExceptionForGraphQl($validatorException, "\n");

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($validatorException, $result->getPrevious());
    }

    public function testProcessStandardInputExceptionForGraphQlWithErrors(): void
    {
        $error1 = $this->getMockBuilder(LocalizedException::class)
            ->setConstructorArgs([new Phrase('Error 1')])
            ->getMock();

        $error2 = $this->getMockBuilder(LocalizedException::class)
            ->setConstructorArgs([new Phrase('Error 2')])
            ->getMock();

        $inputException = $this->getMockBuilder(InputException::class)
            ->setConstructorArgs([new Phrase('Main message')])
            ->onlyMethods(['getErrors'])
            ->getMock();
        $inputException->expects($this->once())
            ->method('getErrors')
            ->willReturn([$error1, $error2]);

        $result = $this->processor->processStandardInputExceptionForGraphQl($inputException, "\n");

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($inputException, $result->getPrevious());
    }

    public function testProcessStandardInputExceptionForGraphQlWithoutErrors(): void
    {
        $inputException = $this->getMockBuilder(InputException::class)
            ->setConstructorArgs([new Phrase('Main error message')])
            ->onlyMethods(['getErrors'])
            ->getMock();
        $inputException->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $result = $this->processor->processStandardInputExceptionForGraphQl($inputException);

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($inputException, $result->getPrevious());
    }

    public function testProcessInputExceptionForGraphQlRoutesToValidatorException(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Fallback message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $result = $this->processor->processInputExceptionForGraphQl($validatorException);

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($validatorException, $result->getPrevious());
    }

    public function testProcessInputExceptionForGraphQlRoutesToStandardInputException(): void
    {
        $inputException = $this->getMockBuilder(InputException::class)
            ->setConstructorArgs([new Phrase('Standard error')])
            ->onlyMethods(['getErrors'])
            ->getMock();
        $inputException->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $result = $this->processor->processInputExceptionForGraphQl($inputException);

        $this->assertInstanceOf(GraphQlInputException::class, $result);
        $this->assertSame($inputException, $result->getPrevious());
    }

    public function testProcessValidatorExceptionForGraphQlWithCustomSeparator(): void
    {
        $errorMessage1 = $this->createMock(AbstractMessage::class);
        $errorMessage1->expects($this->once())
            ->method('getText')
            ->willReturn('Error 1');

        $errorMessage2 = $this->createMock(AbstractMessage::class);
        $errorMessage2->expects($this->once())
            ->method('getText')
            ->willReturn('Error 2');

        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage1, $errorMessage2]);

        $result = $this->processor->processValidatorExceptionForGraphQl($validatorException, "\n");

        $this->assertInstanceOf(GraphQlInputException::class, $result);
    }
}
