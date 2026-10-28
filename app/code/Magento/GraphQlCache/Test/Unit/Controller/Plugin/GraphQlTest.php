<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Test\Unit\Controller\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Registry;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQlCache\Controller\Plugin\GraphQl;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GraphQlTest extends TestCase
{
    /**
     * @var CacheableQuery|MockObject
     */
    private CacheableQuery|MockObject $cacheableQueryMock;

    /**
     * @var Config|MockObject
     */
    private Config|MockObject $configMock;

    /**
     * @var CacheIdCalculator|MockObject
     */
    private CacheIdCalculator|MockObject $cacheIdCalculatorMock;

    /**
     * @var HttpRequestProcessor|MockObject
     */
    private HttpRequestProcessor|MockObject $requestProcessorMock;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registryMock;

    /**
     * @var GraphQl
     */
    private GraphQl $graphQl;

    protected function setUp(): void
    {
        $this->cacheableQueryMock = $this->createMock(CacheableQuery::class);
        $this->cacheIdCalculatorMock = $this->createMock(CacheIdCalculator::class);
        $this->configMock = $this->createMock(Config::class);
        $this->requestProcessorMock = $this->createMock(HttpRequestProcessor::class);
        $this->registryMock = $this->createMock(Registry::class);
        $this->graphQl = new GraphQl(
            $this->cacheableQueryMock,
            $this->cacheIdCalculatorMock,
            $this->configMock,
            $this->requestProcessorMock,
            $this->registryMock,
        );
    }

    public function testBeforeDispatch(): void
    {
        $subject = $this->createMock(FrontControllerInterface::class);
        $requestMock = $this->createMock(Http::class);
        $this->graphQl->beforeDispatch($subject, $requestMock);
    }

    public function testBeforeDispatchInvalidRequest(): void
    {
        $subject = $this->createMock(FrontControllerInterface::class);
        $requestMock = $this->createMock(Http::class);
        $this->requestProcessorMock->expects(self::once())
            ->method('validateRequest')
            ->willThrowException(new GraphQlInputException(__('')));
        $this->requestProcessorMock->expects(self::never())->method('processHeaders');
        $this->graphQl->beforeDispatch($subject, $requestMock);
    }
}
