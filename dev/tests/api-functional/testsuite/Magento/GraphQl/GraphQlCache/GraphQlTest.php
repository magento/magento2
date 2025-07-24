<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GraphQlCache;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Framework\Registry;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQlCache\Controller\Plugin\GraphQl;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class GraphQlTest extends GraphQlAbstract
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

    /**
     * @var Registry
     */
    private $registryMock;

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
        $this->registryMock = $this->createMock(Registry::class);
        $this->subjectMock = $this->createMock(FrontControllerInterface::class);
        $this->requestMock = $this
            ->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->graphql = new GraphQl(
            $this->cacheableQueryMock,
            $this->cacheIdCalculatorMock,
            $this->configMock,
            $this->loggerMock,
            $this->requestProcessorMock,
            $this->registryMock
        );
    }
    #[
        DataFixture(Customer::class, as: 'customer'),
    ]
    public function testMutation(): void
    {
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = DataFixtureStorageManager::getStorage()->get('customer');
        $generateToken = <<<MUTATION
        mutation{
            generateCustomerToken
            (
                email:"{$customer->getEmail()}",
                password: "password"
            )
            {
                token
            }
        }
MUTATION;
        $tokenResponse = $this->graphQlMutationWithResponseHeaders($generateToken);
        $this->assertEquals('no-cache', $tokenResponse['headers']['Pragma']);
        $this->assertEquals(
            'no-store, no-cache, must-revalidate, max-age=0',
            $tokenResponse['headers']['Cache-Control']
        );
    }

    public function testBeforeDispatch(): void
    {
        $this->requestProcessorMock
            ->expects($this->once())
            ->method('validateRequest');
        $this->requestProcessorMock
            ->expects($this->once())
            ->method('processHeaders');
        $this->loggerMock
            ->expects($this->never())
            ->method('critical');
        $this->assertNull($this->graphql->beforeDispatch($this->subjectMock, $this->requestMock));
    }

    public function testBeforeDispatchForException(): void
    {
        $this->requestProcessorMock
            ->expects($this->once())
            ->method('validateRequest')
            ->willThrowException(new \Exception('Invalid Headers'));
        $this->requestProcessorMock
            ->expects($this->never())
            ->method('processHeaders');
        $this->loggerMock
            ->expects($this->once())
            ->method('critical');
        $this->assertNull($this->graphql->beforeDispatch($this->subjectMock, $this->requestMock));
    }
}
