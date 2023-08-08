<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Test\Unit\Controller\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQlCache\Controller\Plugin\GraphQl;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test beforeDispatch
 */
class GraphQlTest extends TestCase
{
    /**
     * @var GraphQl
     */
    private $graphql;

    /**
     * @var CacheableQuery|MockObject
     */
    private $cacheableQueryMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ResponseHttp|MockObject
     */
    private $responseMock;

    /**
     * @var HttpRequestProcessor|MockObject
     */
    private $requestProcessorMock;

    /**
     * @var CacheIdCalculator|MockObject
     */
    private $cacheIdCalculatorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var FrontControllerInterface|MockObject
     */
    private $subjectMock;

    /**
     * @var Http|MockObject
     */
    private $requestMock;

    protected function setUp(): void
    {
        $this->cacheableQueryMock = $this->createMock(CacheableQuery::class);
        $this->cacheIdCalculatorMock = $this->createMock(CacheIdCalculator::class);
        $this->configMock = $this->createMock(Config::class);
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->onlyMethods(['critical'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestProcessorMock = $this->getMockBuilder(HttpRequestProcessor::class)
            ->onlyMethods(['validateRequest','processHeaders'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->responseMock = $this->createMock(ResponseHttp::class);
        $this->subjectMock = $this->createMock(FrontControllerInterface::class);
        $this->requestMock = $this->createMock(Http::class);
        $this->graphql = new GraphQl(
            $this->cacheableQueryMock,
            $this->cacheIdCalculatorMock,
            $this->configMock,
            $this->loggerMock,
            $this->requestProcessorMock,
            $this->responseMock
        );
    }

    /**
     * test beforeDispatch function for validation purpose
     */
    public function testBeforeDispatch(): void
    {
        $this->requestProcessorMock
            ->expects($this->any())
            ->method('validateRequest');
        $this->requestProcessorMock
            ->expects($this->any())
            ->method('processHeaders');
        $this->loggerMock
            ->expects($this->any())
            ->method('critical');
        $this->assertNull($this->graphql->beforeDispatch($this->subjectMock, $this->requestMock));
    }
}
