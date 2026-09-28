<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\Oauth\OauthInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestProcessorPool;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Controller\Rest\SchemaRequestProcessor;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor;
use Magento\Webapi\Model\Rest\Swagger\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test Rest controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RestTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Rest
     */
    protected $_restController;

    /**
     * @var Request|MockObject
     */
    protected $_requestMock;

    /**
     * @var Response|MockObject
     */
    protected $_responseMock;

    /**
     * @var MockObject|Route
     */
    protected $_routeMock;

    /**
     * @var \stdClass|MockObject
     */
    protected $_serviceMock;

    /**
     * @var OauthInterface|MockObject
     */
    protected $_oauthServiceMock;

    /**
     * @var Authorization|MockObject
     */
    protected $_authorizationMock;

    /**
     * @var ServiceInputProcessor|MockObject
     */
    protected $serviceInputProcessorMock;

    /**
     * @var Generator|MockObject
     */
    protected $swaggerGeneratorMock;

    /**
     * @var  StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var  StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var  SchemaRequestProcessor|MockObject
     */
    protected $schemaRequestProcessor;

    /**
     * @var  SynchronousRequestProcessor|MockObject
     */
    protected $synchronousRequestProcessor;

    /**
     * @var  RequestProcessorPool|MockObject
     */
    protected $requestProcessorPool;

    private const SERVICE_METHOD = 'testMethod';

    private const SERVICE_ID = Rest::class;

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->_requestMock = $this->getRequestMock();
        $this->_requestMock->expects($this->any())->method('getHttpHost')->willReturn('testHostName.com');
        $this->_responseMock = $this->getResponseMock();
        $routerMock = $this->createPartialMock(
            Router::class,
            ['match']
        );

        $this->_routeMock = $this->getRouteMock();
        $this->_serviceMock = $this->createPartialMockWithReflection(
            self::SERVICE_ID,
            [self::SERVICE_METHOD]
        );

        $this->_oauthServiceMock = $this->createStub(OauthInterface::class);
        $this->_authorizationMock = $this->createMock(Authorization::class);

        $paramsOverriderMock = $this->createPartialMockWithReflection(
            ParamsOverrider::class,
            ['overrideParams']
        );

        $dataObjectProcessorMock = $this->createPartialMockWithReflection(
            DataObjectProcessor::class,
            ['getMethodReturnType']
        );

        $layoutMock = $this->createMock(LayoutInterface::class);

        $errorProcessorMock = $this->createMock(ErrorProcessor::class);
        $errorProcessorMock->expects($this->any())->method('maskException')->willReturnArgument(0);

        $objectManager = new ObjectManager($this);

        $this->serviceInputProcessorMock = $this->createPartialMock(
            ServiceInputProcessor::class,
            ['process']
        );

        $areaListMock = $this->createMock(AreaList::class);
        $areaMock = $this->createMock(AreaInterface::class);
        $areaListMock->expects($this->any())->method('getArea')->willReturn($areaMock);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->requestProcessorPool = $this->getRequestProccessotPoolMock();

        $this->_restController =
            $objectManager->getObject(
                Rest::class,
                [
                    'request'               => $this->_requestMock,
                    'response'              => $this->_responseMock,
                    'router'                => $routerMock,
                    'objectManager'         => $objectManagerMock,
                    'layout'                => $layoutMock,
                    'oauthService'          => $this->_oauthServiceMock,
                    'authorization'         => $this->_authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'errorProcessor'        => $errorProcessorMock,
                    'areaList'              => $areaListMock,
                    'paramsOverrider'       => $paramsOverriderMock,
                    'dataObjectProcessor'   => $dataObjectProcessorMock,
                    'storeManager'          => $this->storeManagerMock,
                    'requestProcessorPool'  => $this->requestProcessorPool,
                ]
            );

        $this->_routeMock->expects($this->any())->method('getServiceClass')->willReturn(self::SERVICE_ID);
        $this->_routeMock->expects($this->any())->method('getServiceMethod')
            ->willReturn(self::SERVICE_METHOD);

        $routerMock->expects($this->any())->method('match')->willReturn($this->_routeMock);

        $objectManagerMock->expects($this->any())->method('get')->willReturn($this->_serviceMock);
        $this->_responseMock->expects($this->any())->method('prepareResponse')->willReturn([]);
        $this->_serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->willReturn(null);

        $dataObjectProcessorMock->expects($this->any())->method('getMethodReturnType')
            ->with(self::SERVICE_ID, self::SERVICE_METHOD)
            ->willReturn('null');

        $paramsOverriderMock->expects($this->any())->method('overrideParams')->willReturn([]);

        parent::setUp();
    }

    public function testDispatchSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'foo',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(SchemaRequestProcessor::PROCESSOR_PATH);

        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($params);

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->_requestMock)->process($this->_requestMock);

        $this->assertEquals($schema, $this->_responseMock->getBody());
    }

    public function testDispatchAllSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'all',
        ];
        $this->_requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(SchemaRequestProcessor::PROCESSOR_PATH);
        $this->_requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    [
                        \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES,
                        null,
                        'all',
                    ],
                ]
            );
        $this->_requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($params);
        $this->_requestMock->expects($this->any())
            ->method('getRequestedServices')
            ->willReturn('all');

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->_requestMock)->process($this->_requestMock);

        $this->assertEquals($schema, $this->_responseMock->getBody());
    }

    /**
     * @return object|RequestProcessorPool
     */
    private function getRequestProccessotPoolMock()
    {
        $objectManager = new ObjectManager($this);

        $this->swaggerGeneratorMock = $this->createPartialMock(
            Generator::class,
            ['generate', 'getListOfServices']
        );

        $this->schemaRequestProcessor = $objectManager->getObject(
            SchemaRequestProcessor::class,
            [
                'swaggerGenerator' => $this->swaggerGeneratorMock,
                'response'         => $this->_responseMock,
            ]
        );

        $this->synchronousRequestProcessor = $this->createPartialMock(
            SynchronousRequestProcessor::class,
            ['process']
        );

        return $objectManager->getObject(
            RequestProcessorPool::class,
            [
                'requestProcessors' => [
                    'syncSchema' => $this->schemaRequestProcessor,
                    'sync'       => $this->synchronousRequestProcessor,
                ],
            ]
        );
    }

    /**
     * @return Route|MockObject
     */
    private function getRouteMock()
    {
        return $this->createPartialMock(
            Route::class,
            [
                'isSecure',
                'getServiceMethod',
                'getServiceClass',
                'getAclResources',
                'getParameters',
            ]
        );
    }

    /**
     * @return Request|MockObject
     */
    private function getRequestMock()
    {
        return $this->createPartialMock(
            Request::class,
            [
                'isSecure',
                'getRequestData',
                'getParams',
                'getParam',
                'getRequestedServices',
                'getPathInfo',
                'getHttpHost',
                'getMethod',
            ]
        );
    }

    /**
     * @return Response|MockObject
     */
    private function getResponseMock()
    {
        return $this->createPartialMock(
            Response::class,
            ['sendResponse', 'prepareResponse', 'setHeader']
        );
    }
}
