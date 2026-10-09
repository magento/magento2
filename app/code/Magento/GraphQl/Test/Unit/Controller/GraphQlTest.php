<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Controller;

use GraphQL\Error\FormattedError;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Fields as QueryFields;
use Magento\Framework\GraphQl\Query\QueryParser;
use Magento\Framework\GraphQl\Query\QueryProcessor;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Response;
use Magento\GraphQl\Controller\GraphQl;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQl\Helper\Query\Logger\LogData;
use Magento\GraphQl\Model\GraphQl\RequestConfiguration;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\Logger\LoggerPool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GraphQlTest extends TestCase
{
    /**
     * @var State|Stub
     */
    private $appState;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var HttpRequestProcessor|Stub
     */
    private $requestProcessor;

    /**
     * @var Json|MockObject
     */
    private $jsonResult;

    /**
     * @var GraphQl
     */
    private $controller;

    protected function setUp(): void
    {
        $this->appState = $this->createStub(State::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->requestProcessor = $this->createStub(HttpRequestProcessor::class);
        $this->jsonResult = $this->createMock(Json::class);
        $jsonFactory = $this->createStub(JsonFactory::class);
        $jsonFactory->method('create')->willReturn($this->jsonResult);
        $areaList = $this->createStub(AreaList::class);
        $areaList->method('getArea')->willReturn($this->createStub(AreaInterface::class));
        $requestConfiguration = $this->createStub(RequestConfiguration::class);
        $requestConfiguration->method('getMaxRequestBodySize')->willReturn(0);

        $this->controller = new GraphQl(
            $this->createStub(Response::class),
            $this->createStub(SchemaGeneratorInterface::class),
            $this->createStub(SerializerInterface::class),
            $this->createStub(QueryProcessor::class),
            new ExceptionFormatter($this->appState, $this->createStub(ErrorProcessor::class), $this->logger),
            $this->createStub(ContextInterface::class),
            $this->requestProcessor,
            $this->createStub(QueryFields::class),
            $jsonFactory,
            $this->createStub(HttpResponse::class),
            $this->createStub(ContextFactoryInterface::class),
            $this->createStub(LogData::class),
            $this->createStub(LoggerPool::class),
            $areaList,
            $this->createStub(QueryParser::class),
            $requestConfiguration
        );
    }

    #[DataProvider('clientSafeExceptionDataProvider')]
    public function testClientSafeExceptionIsNotLoggedInProductionMode(\Exception $exception, int $statusCode): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->requestProcessor->method('validateRequest')->willThrowException($exception);
        $this->logger->expects(self::never())->method('critical');
        $this->jsonResult->expects(self::once())->method('setHttpResponseCode')->with($statusCode);
        $this->jsonResult->expects(self::once())
            ->method('setData')
            ->with(['errors' => [FormattedError::createFromException($exception)]]);

        $this->controller->dispatch($this->createStub(Http::class));
    }

    public static function clientSafeExceptionDataProvider(): array
    {
        return [
            [new GraphQlAuthenticationException(__('User token has been revoked')), 401],
            [new GraphQlAuthorizationException(__('The current customer isn\'t authorized.')), 403],
        ];
    }

    #[DataProvider('clientUnsafeExceptionDataProvider')]
    public function testClientUnsafeExceptionIsLoggedInProductionMode(\Exception $exception, int $statusCode): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_PRODUCTION);
        $this->requestProcessor->method('validateRequest')->willThrowException($exception);
        $this->logger->expects(self::once())->method('critical');
        $this->jsonResult->expects(self::once())->method('setHttpResponseCode')->with($statusCode);
        $this->jsonResult->expects(self::once())
            ->method('setData')
            ->with(['errors' => [FormattedError::createFromException($exception)]]);

        $this->controller->dispatch($this->createStub(Http::class));
    }

    public static function clientUnsafeExceptionDataProvider(): array
    {
        return [
            [new GraphQlAuthenticationException(__('Authentication failed'), null, 0, false), 401],
            [new GraphQlAuthorizationException(__('Authorization failed'), null, 0, false), 403],
        ];
    }

    public function testClientSafeExceptionKeepsDebugDetailsInDeveloperMode(): void
    {
        $this->appState->method('getMode')->willReturn(State::MODE_DEVELOPER);
        $this->requestProcessor->method('validateRequest')
            ->willThrowException(new GraphQlAuthenticationException(__('User token has been revoked')));
        $this->logger->expects(self::never())->method('critical');
        $this->jsonResult->expects(self::once())->method('setHttpResponseCode')->with(401);
        $this->jsonResult->expects(self::once())
            ->method('setData')
            ->with(self::callback(fn (array $data): bool => isset($data['errors'][0]['extensions']['trace'])));

        $this->controller->dispatch($this->createStub(Http::class));
    }
}
