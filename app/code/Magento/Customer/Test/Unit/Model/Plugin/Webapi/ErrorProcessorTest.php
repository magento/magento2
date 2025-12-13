<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin\Webapi;

use Magento\Customer\Model\Plugin\Webapi\ErrorProcessorPlugin;
use Magento\Customer\Model\ValidatorExceptionProcessor;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\AbstractMessage;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Validator\Exception as ValidatorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ErrorProcessorTest extends TestCase
{
    /**
     * @var ErrorProcessorPlugin
     */
    private $plugin;

    /**
     * @var ValidatorExceptionProcessor|MockObject
     */
    private $validatorExceptionProcessorMock;

    /**
     * @var State|MockObject
     */
    private $appStateMock;

    /**
     * @var ErrorProcessor|MockObject
     */
    private $errorProcessorMock;

    protected function setUp(): void
    {
        $this->validatorExceptionProcessorMock = $this->createMock(ValidatorExceptionProcessor::class);
        $this->appStateMock = $this->createMock(State::class);
        $this->errorProcessorMock = $this->createMock(ErrorProcessor::class);
        $this->plugin = new ErrorProcessorPlugin(
            $this->validatorExceptionProcessorMock,
            $this->appStateMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testAroundMaskExceptionWithValidatorException(): void
    {
        $errorMessage1 = $this->createMock(AbstractMessage::class);
        $errorMessage2 = $this->createMock(AbstractMessage::class);

        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Combined error message')])
            ->onlyMethods(['getMessages', 'getParameters'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage1, $errorMessage2]);
        $validatorException->expects($this->once())
            ->method('getParameters')
            ->willReturn([]);

        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $result = [
            'errors' => [
                new LocalizedException(new Phrase('Der Vorname ist ungültig!')),
                new LocalizedException(new Phrase('Der Nachname ist ungültig!'))
            ],
            'mainPhrase' => new Phrase("Der Vorname ist ungültig!\nDer Nachname ist ungültig!")
        ];

        $this->validatorExceptionProcessorMock->expects($this->once())
            ->method('processValidatorExceptionForRestApi')
            ->with($validatorException)
            ->willReturn($result);

        /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
        $proceed = function ($exception) {
            return $this->createMock(WebapiException::class);
        };

        $maskedException = $this->plugin->aroundMaskException(
            $this->errorProcessorMock,
            $proceed,
            $validatorException
        );

        $this->assertInstanceOf(WebapiException::class, $maskedException);
        $this->assertEquals(WebapiException::HTTP_BAD_REQUEST, $maskedException->getHttpCode());
        $errors = $maskedException->getErrors();
        $this->assertIsArray($errors);
        $this->assertCount(2, $errors);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testAroundMaskExceptionWithNonValidatorException(): void
    {
        $regularException = new \Exception('Regular exception');

        $expectedException = $this->createMock(WebapiException::class);
        $proceed = function ($exception) use ($expectedException) {
            return $expectedException;
        };

        $result = $this->plugin->aroundMaskException(
            $this->errorProcessorMock,
            $proceed,
            $regularException
        );

        $this->assertSame($expectedException, $result);
        $this->validatorExceptionProcessorMock->expects($this->never())
            ->method('processValidatorExceptionForRestApi');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testAroundMaskExceptionWithValidatorExceptionEmptyMessages(): void
    {
        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Combined error message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->validatorExceptionProcessorMock->expects($this->never())
            ->method('processValidatorExceptionForRestApi');

        $expectedException = $this->createMock(WebapiException::class);

        /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
        $proceed = function ($exception) use ($expectedException) {
            return $expectedException;
        };

        $maskedException = $this->plugin->aroundMaskException(
            $this->errorProcessorMock,
            $proceed,
            $validatorException
        );

        $this->assertSame($expectedException, $maskedException);
        $this->validatorExceptionProcessorMock->expects($this->never())
            ->method('processValidatorExceptionForRestApi');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testAroundMaskExceptionWithValidatorExceptionNotLocalizedException(): void
    {
        $errorMessage = $this->createMock(AbstractMessage::class);

        $validatorException = $this->getMockBuilder(ValidatorException::class)
            ->setConstructorArgs([new Phrase('Test message')])
            ->onlyMethods(['getMessages'])
            ->getMock();
        $validatorException->expects($this->once())
            ->method('getMessages')
            ->willReturn([$errorMessage]);

        $expectedException = $this->createMock(WebapiException::class);

        $result = $this->plugin->aroundMaskException(
            $this->errorProcessorMock,
            function () use ($expectedException) {
                return $expectedException;
            },
            $validatorException
        );

        $this->assertSame($expectedException, $result);
        $this->validatorExceptionProcessorMock->expects($this->never())
            ->method('processValidatorExceptionForRestApi');
    }
}
