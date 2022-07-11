<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Setup\Exception;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Webapi\Controller\Rest\InputParamsResolver;
use Magento\Webapi\Controller\Rest\Router\Route;

class SynchronousRequestProcessorTest extends TestCase
{
    /**
     * @var RestResponse|MockObject
     */
    private $restResponseMock;

    /**
     * @var InputParamsResolver|MockObject
     */
    private $inputParamsResolverMock;

    /**
     * @var ServiceOutputProcessor|MockObject
     */
    private $serviceOutputProcessorMock;

    /**
     * @var FieldsFilter|MockObject
     */
    private $fieldsFilterMock;

    /**
     * @var DeploymentConfig|MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var SynchronousRequestProcessor
     */
    private $requestProcessor;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->restResponseMock = $this->getMockBuilder(RestResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputParamsResolverMock = $this->getMockBuilder(InputParamsResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serviceOutputProcessorMock = $this->getMockBuilder(ServiceOutputProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldsFilterMock = $this->getMockBuilder(FieldsFilter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->requestProcessor =
            $objectManager->getObject(
                SynchronousRequestProcessor::class,
                [
                    'response' => $this->restResponseMock,
                    'inputParamsResolver' => $this->inputParamsResolverMock,
                    'serviceOutputProcessor' => $this->serviceOutputProcessorMock,
                    'fieldsFilter' => $this->fieldsFilterMock,
                    'deploymentConfig' => $this->deploymentConfigMock,
                    'objectManager' => $this->objectManagerMock
                ]
            );
    }

    private function getCustomObject()
    {
        return new class {
            public function getException() {
                throw new \Exception('Internal error');
            }
        };
    }

    /**
     * Test exception output
     */
    public function testExceptionOutput()
    {
        $requestMock = $this->getMockBuilder(Request::class)
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
            )->disableOriginalConstructor()
            ->getMock();
        $requestMock->expects($this->any())
            ->method('getHttpHost')
            ->willReturn('example.com');

        $routeMock = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routeMock->expects($this->once())
            ->method('getServiceMethod')->willReturn('getException');

        $this->inputParamsResolverMock->expects($this->once())
            ->method('getRoute')->willReturn($routeMock);

        $this->inputParamsResolverMock->expects($this->once())
            ->method('resolve')->willReturn([]);

        $this->objectManagerMock->expects($this->once())
            ->method('get')->willReturn($this->getCustomObject());

        $this->expectException('Magento\Framework\Webapi\Exception');
        $this->requestProcessor->process($requestMock);
    }
}
