<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Model\ValidatorExceptionProcessor;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\Exception as ValidatorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidatorExceptionProcessorTest extends TestCase
{
    /**
     * @var ValidatorExceptionProcessor
     */
    private $processor;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->processor = new ValidatorExceptionProcessor($this->messageManagerMock);
    }

    public function testProcessValidatorExceptionWithMessages(): void
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

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(function ($message) {
                return is_string($message) &&
                    (str_contains($message, 'First Name is not valid!') ||
                        str_contains($message, 'Der Vorname ist ungültig!')) &&
                    (str_contains($message, 'Last Name is not valid!') ||
                        str_contains($message, 'Der Nachname ist ungültig!'));
            }));

        $this->processor->processValidatorException($validatorException);
    }

    public function testProcessValidatorExceptionWithEmptyMessages(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Combined error message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processValidatorException($validatorException);
    }

    public function testProcessValidatorExceptionWithStringMessages(): void
    {
        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn(['First Name is not valid!', 'Last Name is not valid!']);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processValidatorException($validatorException);
    }

    public function testProcessStandardInputExceptionWithErrors(): void
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

        $this->messageManagerMock->expects($this->exactly(3))
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processStandardInputException($inputException);
    }

    public function testProcessStandardInputExceptionWithoutErrors(): void
    {
        $inputException = $this->getMockBuilder(InputException::class)
            ->setConstructorArgs([new Phrase('Main error message')])
            ->onlyMethods(['getErrors'])
            ->getMock();
        $inputException->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processStandardInputException($inputException);
    }

    public function testProcessInputExceptionRoutesToValidatorException(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Fallback message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processInputException($validatorException);
    }

    public function testProcessInputExceptionRoutesToStandardInputException(): void
    {
        $inputException = $this->getMockBuilder(InputException::class)
            ->setConstructorArgs([new Phrase('Standard error')])
            ->onlyMethods(['getErrors'])
            ->getMock();
        $inputException->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processInputException($inputException);
    }

    public function testProcessValidatorExceptionWithMessageFormatter(): void
    {
        $errorMessage = $this->createMock(AbstractMessage::class);
        $errorMessage->expects($this->once())
            ->method('getText')
            ->willReturn('First Name is not valid!');

        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage]);

        $formatter = function ($message) {
            return 'FORMATTED: ' . $message;
        };

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->stringContains('FORMATTED:'));

        $this->processor->processValidatorException($validatorException, $formatter);
    }

    public function testProcessValidatorExceptionWithAddErrorMethod(): void
    {
        $errorMessage = $this->createMock(AbstractMessage::class);
        $errorMessage->expects($this->once())
            ->method('getText')
            ->willReturn('First Name is not valid!');

        $validatorException = $this->createMock(ValidatorException::class);
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage]);

        $this->messageManagerMock->expects($this->once())
            ->method('addError')
            ->with($this->callback(fn($value) => is_string($value)));

        $this->processor->processValidatorException($validatorException, null, 'addError');
    }

    public function testProcessValidatorExceptionForRestApi(): void
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

        $result = $this->processor->processValidatorExceptionForRestApi($validatorException);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('mainPhrase', $result);
        $this->assertIsArray($result['errors']);
        $this->assertCount(2, $result['errors']);
        $this->assertInstanceOf(Phrase::class, $result['mainPhrase']);
    }

    public function testProcessValidatorExceptionForRestApiWithEmptyMessages(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Combined error message')])
            ->onlyMethods(['getMessages', 'getRawMessage'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);
        $validatorException->expects($this->once())
            ->method('getRawMessage')
            ->willReturn('Combined error message');

        $result = $this->processor->processValidatorExceptionForRestApi($validatorException);

        $this->assertIsArray($result);
        $this->assertNull($result['errors']);
        $this->assertInstanceOf(Phrase::class, $result['mainPhrase']);
    }
}
