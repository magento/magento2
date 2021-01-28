<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Controller;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\WebapiAsync\Controller\Rest\AsynchronousRequestProcessor;

/**
 * Test Rest controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Rest
     */
    private $restController;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    private $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | \Magento\Webapi\Controller\Rest\Router\Route
     */
    private $routeMock;

    /**
     * @var \stdClass|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serviceMock;

    /**
     * @var \Magento\Framework\Oauth\OauthInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $oauthServiceMock;

    /**
     * @var \Magento\Framework\Webapi\Authorization|\PHPUnit\Framework\MockObject\MockObject
     */
    private $authorizationMock;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $serviceInputProcessorMock;

    /**
     * @var \Magento\Webapi\Model\Rest\Swagger\Generator | \PHPUnit\Framework\MockObject\MockObject
     */
    private $swaggerGeneratorMock;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;

    /**
     * @var  \Magento\Store\Api\Data\StoreInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $storeMock;

    /**
     * @var  \Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor |
     *     \PHPUnit\Framework\MockObject\MockObject
     */
    private $asyncSchemaRequestProcessor;

    /**
     * @var  \Magento\WebapiAsync\Controller\Rest\AsynchronousRequestProcessor |
     *     \PHPUnit\Framework\MockObject\MockObject
     */
    private $asyncRequestProcessor;

    /**
     * @var  \Magento\Webapi\Controller\Rest\RequestProcessorPool | \PHPUnit\Framework\MockObject\MockObject
     */
    private $requestProcessorPool;

    const SERVICE_METHOD = 'testMethod';

    const SERVICE_ID = \Magento\Webapi\Controller\Rest::class;

    protected function setUp(): void
    {
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->requestMock = $this->getRequestMock();
        $this->requestMock->expects($this->any())->method('getHttpHost')->willReturn('testHostName.com');
        $this->responseMock = $this->getResponseMock();
        $routerMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router::class)->setMethods(['match'])
            ->disableOriginalConstructor()->getMock();

        $this->routeMock = $this->getRouteMock();
        $this->serviceMock = $this->getMockBuilder(self::SERVICE_ID)->setMethods([self::SERVICE_METHOD])
            ->disableOriginalConstructor()->getMock();

        $this->oauthServiceMock = $this->getMockBuilder(\Magento\Framework\Oauth\OauthInterface::class)
            ->setMethods(['validateAccessTokenRequest'])->getMockForAbstractClass();
        $this->authorizationMock = $this->getMockBuilder(\Magento\Framework\Webapi\Authorization::class)
            ->disableOriginalConstructor()->getMock();

        $paramsOverriderMock = $this->getMockBuilder(\Magento\Webapi\Controller\Rest\ParamsOverrider::class)
            ->setMethods(['overrideParams'])
            ->disableOriginalConstructor()->getMock();

        $dataObjectProcessorMock = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodReturnType'])
            ->getMockForAbstractClass();

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()->getMock();

        $errorProcessorMock = $this->createMock(\Magento\Framework\Webapi\ErrorProcessor::class);
        $errorProcessorMock->expects($this->any())->method('maskException')->willReturnArgument(0);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->serviceInputProcessorMock = $this->getMockBuilder(\Magento\Framework\Webapi\ServiceInputProcessor::class)
            ->disableOriginalConstructor()->setMethods(['process'])->getMock();

        $areaListMock = $this->createMock(\Magento\Framework\App\AreaList::class);
        $areaMock = $this->createMock(\Magento\Framework\App\AreaInterface::class);
        $areaListMock->expects($this->any())->method('getArea')->willReturn($areaMock);
        $this->storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->requestProcessorPool = $this->getRequestProccessotPoolMock();

        $this->restController =
            $objectManager->getObject(
                \Magento\Webapi\Controller\Rest::class,
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
     * @return object|\Magento\Webapi\Controller\Rest\RequestProcessorPool
     */
    private function getRequestProccessotPoolMock()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->swaggerGeneratorMock = $this->getMockBuilder(\Magento\Webapi\Model\Rest\Swagger\Generator::class)
            ->disableOriginalConstructor()
            ->setMethods(['generate', 'getListOfServices'])
            ->getMockForAbstractClass();

        $this->asyncSchemaRequestProcessor = $objectManager->getObject(
            \Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor::class,
            [
                'swaggerGenerator' => $this->swaggerGeneratorMock,
                'response'         => $this->responseMock,
            ]
        );

        $this->asyncRequestProcessor =
            $this->getMockBuilder(\Magento\WebapiAsync\Controller\Rest\AsynchronousRequestProcessor::class)
                ->setMethods(['process'])
                ->disableOriginalConstructor()
                ->getMock();

        return $objectManager->getObject(
            \Magento\Webapi\Controller\Rest\RequestProcessorPool::class,
            [
                'requestProcessors' => [
                    'asyncSchema' => $this->asyncSchemaRequestProcessor,
                    'async'       => $this->asyncRequestProcessor,
                ],
            ]
        );
    }

    /**
     * @return \Magento\Webapi\Controller\Rest\Router\Route | \PHPUnit\Framework\MockObject\MockObject
     */
    private function getRouteMock()
    {
        return $this->getMockBuilder(\Magento\Webapi\Controller\Rest\Router\Route::class)
            ->setMethods([
                'isSecure',
                'getServiceMethod',
                'getServiceClass',
                'getAclResources',
                'getParameters',
            ])
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \Magento\Framework\Webapi\Rest\Request|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getRequestMock()
    {
        return $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Request::class)
            ->setMethods(
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
            )->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \Magento\Framework\Webapi\Rest\Response|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getResponseMock()
    {
        return $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Response::class)
            ->setMethods(['sendResponse', 'prepareResponse', 'setHeader'])
            ->disableOriginalConstructor()
            ->getMock();
    }
}
