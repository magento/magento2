<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Controller;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\Oauth\OauthInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
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
use Magento\Webapi\Model\Rest\Swagger\Generator;
use Magento\WebapiAsync\Controller\Rest\AsynchronousRequestProcessor;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
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
    /**
     * @var Rest
     */
    private $restController;

    /**
     * @var Request|MockObject
     */
    private $requestMock;

    /**
     * @var Response|MockObject
     */
    private $responseMock;

    /**
     * @var MockObject|Route
     */
    private $routeMock;

    /**
     * @var \stdClass|MockObject
     */
    private $serviceMock;

    /**
     * @var OauthInterface|MockObject
     */
    private $oauthServiceMock;

    /**
     * @var Authorization|MockObject
     */
    private $authorizationMock;

    /**
     * @var ServiceInputProcessor|MockObject
     */
    private $serviceInputProcessorMock;

    /**
     * @var Generator|MockObject
     */
    private $swaggerGeneratorMock;

    /**
     * @var  StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var  StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var  AsynchronousSchemaRequestProcessor|MockObject
     */
    private $asyncSchemaRequestProcessor;

    /**
     * @var  AsynchronousRequestProcessor|MockObject
     */
    private $asyncRequestProcessor;

    /**
     * @var  RequestProcessorPool|MockObject
     */
    private $requestProcessorPool;

    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = Rest::class;

    protected function setUp(): void
    {
        $objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->requestMock = $this->getRequestMock();
        $this->requestMock->expects($this->any())->method('getHttpHost')->willReturn('testHostName.com');
        $this->responseMock = $this->getResponseMock();
        $routerMock = $this->getMockBuilder(Router::class)
            ->onlyMethods(['match'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->routeMock = $this->getRouteMock();
        $this->serviceMock = $this->getMockBuilder(self::SERVICE_ID)
            ->addMethods([self::SERVICE_METHOD])
            ->disableOriginalConstructor()
            ->getMock();

        $this->oauthServiceMock = $this->getMockBuilder(OauthInterface::class)
            ->onlyMethods(['validateAccessTokenRequest'])->getMockForAbstractClass();
        $this->authorizationMock = $this->getMockBuilder(Authorization::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paramsOverriderMock = $this->getMockBuilder(ParamsOverrider::class)
            ->addMethods(['overrideParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->addMethods(['getMethodReturnType'])
            ->getMockForAbstractClass();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $errorProcessorMock = $this->createMock(ErrorProcessor::class);
        $errorProcessorMock->expects($this->any())->method('maskException')->willReturnArgument(0);

        $objectManager = new ObjectManager($this);

        $this->serviceInputProcessorMock = $this->getMockBuilder(ServiceInputProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])->getMock();

        $areaListMock = $this->createMock(AreaList::class);
        $areaMock = $this->getMockForAbstractClass(AreaInterface::class);
        $areaListMock->expects($this->any())->method('getArea')->willReturn($areaMock);
        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->requestProcessorPool = $this->getRequestProccessotPoolMock();

        $this->restController =
            $objectManager->getObject(
                Rest::class,
                [
                    'request'               => $this->requestMock,
                    'response'              => $this->responseMock,
                    'router'                => $routerMock,
                    'objectManager'         => $objectManagerMock,
                    'layout'                => $layoutMock,
                    'oauthService'          => $this->oauthServiceMock,
                    'authorization'         => $this->authorizationMock,
                    'serviceInputProcessor' => $this->serviceInputProcessorMock,
                    'errorProcessor'        => $errorProcessorMock,
                    'areaList'              => $areaListMock,
                    'paramsOverrider'       => $paramsOverriderMock,
                    'dataObjectProcessor'   => $dataObjectProcessorMock,
                    'storeManager'          => $this->storeManagerMock,
                    'requestProcessorPool'  => $this->requestProcessorPool,
                ]
            );

        $this->routeMock->expects($this->any())->method('getServiceClass')->willReturn(self::SERVICE_ID);
        $this->routeMock->expects($this->any())->method('getServiceMethod')
            ->willReturn(self::SERVICE_METHOD);

        $routerMock->expects($this->any())->method('match')->willReturn($this->routeMock);

        $objectManagerMock->expects($this->any())->method('get')->willReturn($this->serviceMock);
        $this->responseMock->expects($this->any())->method('prepareResponse')->willReturn([]);
        $this->serviceMock->expects($this->any())->method(self::SERVICE_METHOD)->willReturn(null);

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
        $this->requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(AsynchronousSchemaRequestProcessor::PROCESSOR_PATH);

        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($params);

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->requestMock)->process($this->requestMock);

        $this->assertEquals($schema, $this->responseMock->getBody());
    }

    public function testDispatchAllSchemaRequest()
    {
        $params = [
            \Magento\Framework\Webapi\Request::REQUEST_PARAM_SERVICES => 'all',
        ];
        $this->requestMock->expects($this->any())
            ->method('getPathInfo')
            ->willReturn(AsynchronousSchemaRequestProcessor::PROCESSOR_PATH);
        $this->requestMock->expects($this->any())
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
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($params);
        $this->requestMock->expects($this->any())
            ->method('getRequestedServices')
            ->willReturn('all');

        $schema = 'Some REST schema content';
        $this->swaggerGeneratorMock->expects($this->any())->method('generate')->willReturn($schema);
        $this->requestProcessorPool->getProcessor($this->requestMock)->process($this->requestMock);

        $this->assertEquals($schema, $this->responseMock->getBody());
    }

    /**
     * @return object|RequestProcessorPool
     */
    private function getRequestProccessotPoolMock()
    {
        $objectManager = new ObjectManager($this);

        $this->swaggerGeneratorMock = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generate', 'getListOfServices'])
            ->getMockForAbstractClass();

        $this->asyncSchemaRequestProcessor = $objectManager->getObject(
            AsynchronousSchemaRequestProcessor::class,
            [
                'swaggerGenerator' => $this->swaggerGeneratorMock,
                'response'         => $this->responseMock,
            ]
        );

        $this->asyncRequestProcessor =
            $this->getMockBuilder(AsynchronousRequestProcessor::class)
                ->onlyMethods(['process'])
                ->disableOriginalConstructor()
                ->getMock();

        return $objectManager->getObject(
            RequestProcessorPool::class,
            [
                'requestProcessors' => [
                    'asyncSchema' => $this->asyncSchemaRequestProcessor,
                    'async'       => $this->asyncRequestProcessor,
                ],
            ]
        );
    }

    /**
     * @return Route|MockObject
     */
    private function getRouteMock()
    {
        return $this->getMockBuilder(Route::class)
            ->onlyMethods([
                'isSecure',
                'getServiceMethod',
                'getServiceClass',
                'getAclResources',
                'getParameters',
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Request|MockObject
     */
    private function getRequestMock()
    {
        return $this->getMockBuilder(Request::class)
            ->onlyMethods(
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
            )->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Response|MockObject
     */
    private function getResponseMock()
    {
        return $this->getMockBuilder(Response::class)
            ->onlyMethods(['sendResponse', 'prepareResponse', 'setHeader'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
