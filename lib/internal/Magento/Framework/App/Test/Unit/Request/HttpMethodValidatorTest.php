<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\HttpMethodMap;
use Magento\Framework\App\Request\HttpMethodValidator;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class HttpMethodValidatorTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->rawFactory = $this->createMock(RawFactory::class);
    }

    /**
     * @return void
     */
    public function testRestrictedActionReturnsMethodNotAllowedWithAllowHeader(): void
    {
        $action = $this->createRestrictedAction();
        $raw = $this->createMock(ResultInterface::class);
        $raw->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(405)
            ->willReturnSelf();
        $raw->expects($this->once())
            ->method('setHeader')
            ->with('Allow', 'GET, POST', true)
            ->willReturnSelf();

        $this->rawFactory->expects($this->once())
            ->method('create')
            ->willReturn($raw);
        $this->logger->expects($this->once())
            ->method('debug')
            ->with(
                "URI '/restricted'' cannot be accessed with PUT method ("
                . get_class($action) . ')'
            );

        $request = $this->createRequest('PUT', '/restricted');
        $validator = $this->createValidator();

        try {
            $validator->validate($request, $action);
            $this->fail('Invalid request exception was not thrown.');
        } catch (InvalidRequestException $exception) {
            $this->assertSame($raw, $exception->getReplaceResult());
        }
    }

    /**
     * @return void
     */
    public function testUnmappedMethodOnActionWithoutLimitationsReturnsNotFoundException(): void
    {
        $this->rawFactory->expects($this->never())
            ->method('create');
        $this->logger->expects($this->once())
            ->method('debug');

        $request = $this->createRequest('UNKNOWN', '/unmapped');
        $action = $this->createMock(ActionInterface::class);
        $validator = $this->createValidator();

        try {
            $validator->validate($request, $action);
            $this->fail('Invalid request exception was not thrown.');
        } catch (InvalidRequestException $exception) {
            $this->assertInstanceOf(NotFoundException::class, $exception->getReplaceResult());
        }
    }

    /**
     * @return HttpMethodValidator
     */
    private function createValidator(): HttpMethodValidator
    {
        return new HttpMethodValidator(
            new HttpMethodMap(
                [
                    'GET' => HttpGetActionInterface::class,
                    'POST' => HttpPostActionInterface::class,
                    'PUT' => 'Magento\Framework\App\Action\HttpPutActionInterface',
                ]
            ),
            $this->logger,
            $this->rawFactory
        );
    }

    /**
     * @param string $method
     * @param string $uri
     * @return Http
     */
    private function createRequest(string $method, string $uri): Http
    {
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethod', 'getRequestUri'])
            ->getMock();
        $request->method('getMethod')
            ->willReturn($method);
        $request->method('getRequestUri')
            ->willReturn($uri);

        return $request;
    }

    /**
     * Create an action restricted to GET and POST requests.
     *
     * @return ActionInterface
     */
    private function createRestrictedAction(): ActionInterface
    {
        return new class implements HttpGetActionInterface, HttpPostActionInterface {
            /**
             * @inheritDoc
             */
            public function execute()
            {
                throw new \RuntimeException('The action is not expected to be executed.');
            }
        };
    }
}
