<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query;

use GraphQL\Error\Error;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlServerException;
use Magento\Framework\GraphQl\Query\ErrorHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ErrorHandlerTest extends TestCase
{
    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var MockObject|AppState
     */
    private $appStateMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->appStateMock = $this->createMock(AppState::class);
        $this->errorHandler = new ErrorHandler($this->loggerMock, $this->appStateMock);
    }

    #[DataProvider('errorsDataProvider')]
    public function testHandle(array $errors, string $mode, int $logCallCount): void
    {
        $formatter = fn ($str) => $str;
        $this->appStateMock->expects(self::atLeastOnce())->method('getMode')->willReturn($mode);
        $this->loggerMock->expects(self::exactly($logCallCount))->method('error');
        $this->errorHandler->handle($errors, $formatter);
    }

    public static function errorsDataProvider(): array
    {
        $inputException = new GraphQlInputException(__('Input error'));
        $serverException = new GraphQlServerException(__('Server error'));
        $aggregatedInputException = (new GraphQlInputException(__('Input error')))
            ->addError(new LocalizedException(__('Child input error 1')))
            ->addError(new LocalizedException(__('Child input error 2')));
        $aggregatedServerException = (new InputException(__('Aggregate error')))
            ->addError(__('Child error 1'))
            ->addError(__('Child error 2'));
        $serverErrors = [
            new Error('Error 1', previous: new \RuntimeException('Server error 1')),
            new Error('Error 2', previous: new \RuntimeException('Server error 2')),
        ];
        return [
            [
                $serverErrors, AppState::MODE_DEVELOPER, 2
            ],
            [
                $serverErrors, AppState::MODE_PRODUCTION, 1
            ],
            [
                [new Error('Error 1', extensions: ['category' => 'graphql-input'])], AppState::MODE_DEVELOPER, 0
            ],
            [
                [new Error('Error 1', extensions: ['category' => 'graphql-server'])], AppState::MODE_DEVELOPER, 1
            ],
            [
                [new Error('Error 1', previous: $inputException)], AppState::MODE_DEVELOPER, 0
            ],
            [
                [new Error('Error 1', previous: $serverException)], AppState::MODE_DEVELOPER, 1
            ],
            [
                [new Error('Error 1', previous: $aggregatedInputException)], AppState::MODE_DEVELOPER, 0
            ],
            [
                [new Error('Error 1', previous: $aggregatedServerException)], AppState::MODE_DEVELOPER, 3
            ],
            [
                [new Error('Error 1', previous: new GraphQlNoSuchEntityException(__('No such entity')))],
                AppState::MODE_DEVELOPER,
                0
            ],
            [
                [new Error('Error 1', previous: new GraphQlAuthorizationException(__('Not authorized')))],
                AppState::MODE_DEVELOPER,
                0
            ],
            [
                [new Error('Cannot query field "unknown" on type "Query".')], AppState::MODE_DEVELOPER, 0
            ],
            [
                [new Error('Error 1', previous: new GraphQlInputException(__('Input error'), null, 0, false))],
                AppState::MODE_DEVELOPER,
                0
            ],
            [
                [new Error('Error 1', previous: new GraphQlNoSuchEntityException(__('Unsafe'), null, 0, false))],
                AppState::MODE_DEVELOPER,
                1
            ],
        ];
    }

    public function testHandleReportsAggregatedClientInputErrorsWithoutLogging(): void
    {
        $childErrors = [
            new LocalizedException(__('Child input error 1')),
            new LocalizedException(__('Child input error 2')),
        ];
        $exception = new GraphQlInputException(__('Input error'));
        foreach ($childErrors as $childError) {
            $exception->addError($childError);
        }
        $this->appStateMock->expects(self::atLeastOnce())
            ->method('getMode')
            ->willReturn(AppState::MODE_DEVELOPER);
        $this->loggerMock->expects(self::never())->method('error');

        $formattedErrors = $this->errorHandler->handle(
            [new Error('Error 1', previous: $exception)],
            fn ($error) => $error
        );

        self::assertSame($childErrors, $formattedErrors);
    }

    #[DataProvider('modeDataProvider')]
    public function testHandleLogsFirstServerErrorBehindClientError(string $mode): void
    {
        $clientError = new Error('Error 1', previous: new GraphQlNoSuchEntityException(__('No such entity')));
        $firstServerError = new Error('Error 2', previous: new \RuntimeException('Server error 1'));
        $secondServerError = new Error('Error 3', previous: new \RuntimeException('Server error 2'));
        $errors = [$clientError, $firstServerError, $secondServerError];
        $this->appStateMock->expects(self::atLeastOnce())->method('getMode')->willReturn($mode);
        $loggedErrors = [];
        $this->loggerMock->expects(self::atLeastOnce())->method('error')->willReturnCallback(
            function ($error) use (&$loggedErrors): void {
                $loggedErrors[] = $error;
            }
        );

        $formattedErrors = $this->errorHandler->handle($errors, fn ($error) => $error);

        if ($mode === AppState::MODE_DEVELOPER) {
            self::assertSame([$firstServerError, $secondServerError], $loggedErrors);
            self::assertSame($errors, $formattedErrors);
        } else {
            self::assertSame([$firstServerError], $loggedErrors);
            self::assertSame([$clientError], $formattedErrors);
        }
    }

    public static function modeDataProvider(): array
    {
        return [
            [AppState::MODE_DEVELOPER],
            [AppState::MODE_PRODUCTION],
            [AppState::MODE_DEFAULT],
        ];
    }
}
